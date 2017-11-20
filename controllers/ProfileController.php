<?php

class ProfileController extends ControllerBase
{
    /**
     * Gets profile
     */
    public function index()
    {
        // Verifies if is get request
        $this->initializeGet();

        // gets token
        $token = $this->decodeToken($this->getToken());

        $conditions = "username = :username:";
        $parameters = array(
            "username" => $token->username_username,
        );
        $user = Users::findFirst(
            array(
                $conditions,
                "bind" => $parameters,
                'columns' => 'id, username, firstname, lastname, birthday, phone, mobile, address, city, country, email',
            )
        );
        if (!$user) {
            $this->buildErrorResponse(404, "profile.PROFILE_NOT_FOUND");
        } else {
            $data = $user->toArray();
            $this->buildSuccessResponse(200, "common.SUCCESSFUL_REQUEST", $data);
        }

    }

    /**
     * Updates profile
     */
    public function update()
    {
        // Verifies if is patch request
        $this->initializePatch();

        // Start a transaction
        $this->db->begin();

        if (empty($this->request->getPut("firstname")) || empty($this->request->getPut("lastname"))) {
            $this->buildErrorResponse(400, "common.INCOMPLETE_DATA_RECEIVED");
        } else {
            // gets token
            $token = $this->decodeToken($this->getToken());

            $conditions = "username = :username:";
            $parameters = array(
                "username" => $token->username_username,
            );
            $user = Users::findFirst(
                array(
                    $conditions,
                    "bind" => $parameters,
                )
            );
            if (!$user) {
                $this->buildErrorResponse(404, "profile.PROFILE_NOT_FOUND");
            } else {
                $user->firstname = $this->request->getPut("firstname");
                $user->lastname = $this->request->getPut("lastname");
                $user->email = $this->request->getPut("email");
                $user->phone = $this->request->getPut("phone");
                $user->mobile = $this->request->getPut("mobile");
                $user->address = $this->request->getPut("address");
                $user->birthday = $this->request->getPut("birthday");

                if (!$user->save()) {
                    $this->db->rollback();
                    // Send errors
                    $errors = array();
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $this->buildErrorResponse(400, "profile.PROFILE_COULD_NOT_BE_UPDATED", $errors);
                } else {
                    // Commit the transaction
                    $this->db->commit();

                    $conditions = "username = :username:";
                    $parameters = array(
                        "username" => $token->username_username,
                    );
                    $user = Users::findFirst(
                        array(
                            $conditions,
                            "bind" => $parameters,
                            'columns' => 'level, username, firstname, lastname, birthday, phone, mobile, address, city, country, email',
                        )
                    );
                    $data = $user->toArray();

                    // Register log in another DB
                    $this->registerLog();

                    $this->buildSuccessResponse(200, "profile.PROFILE_UPDATED", $data);
                }

            }
        }
    }

    /**
     * Changes password
     */
    public function changePassword()
    {
        // Verifies if is patch request
        $this->initializePatch();

        // Start a transaction
        $this->db->begin();

        if (empty($this->request->getPut("current_password")) || empty($this->request->getPut("new_password"))) {
            $this->buildErrorResponse(400, "common.INCOMPLETE_DATA_RECEIVED");
        } else {

            // User token
            $token = $this->decodeToken($this->getToken());

            $conditions = "username = :username:";
            $parameters = array(
                "username" => $token->username_username,
            );
            $user = Users::findFirst(
                array(
                    $conditions,
                    "bind" => $parameters,
                )
            );
            if (!$user) {
                $this->buildErrorResponse(400, "common.THERE_HAS_BEEN_AN_ERROR");
            } else {
                // if old password matches
                if (!password_verify($this->request->getPut("current_password"), $user->password)) {
                    $this->buildErrorResponse(400, "change-password.WRONG_CURRENT_PASSWORD");
                } else {
                    // Encrypts temporary password
                    $password_hashed = password_hash($this->request->getPut("new_password"), PASSWORD_BCRYPT);
                    $user->password = $password_hashed;
                    if (!$user->save()) {
                        $this->db->rollback();
                        // Send errors
                        $errors = array();
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $this->buildErrorResponse(400, "change-password.PASSWORD_COULD_NOT_BE_UPDATED", $errors);
                    } else {
                        // Commit the transaction
                        $this->db->commit();

                        // Register log in another DB
                        $this->registerLog();

                        $this->buildSuccessResponse(200, "change-password.PASSWORD_SUCCESSFULLY_UPDATED");
                    }
                }
            }
        }
    }
}
