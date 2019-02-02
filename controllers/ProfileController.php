<?php

class ProfileController extends ControllerBase
{
    /**
     * Private functions
     */
    private function findUser($token)
    {
        $conditions = 'username = :username:';
        $parameters = array(
            'username' => $token->username_username,
        );
        $user = Users::findFirst(
            array(
                $conditions,
                'bind' => $parameters,
            )
        );
        if (!$user) {
            $this->buildErrorResponse(404, 'profile.PROFILE_NOT_FOUND');
        }
        return $user;
    }

    private function updateProfile($user, $firstname, $lastname, $email, $phone, $mobile, $address, $birthday)
    {
        $user->firstname = trim($firstname);
        $user->lastname = trim($lastname);
        $user->email = trim($email);
        $user->phone = trim($phone);
        $user->mobile = trim($mobile);
        $user->address = trim($address);
        $user->birthday = trim($birthday);
        $this->tryToSaveData($user, 'profile.PROFILE_COULD_NOT_BE_UPDATED');
        return $user;
    }

    private function checkPassword($password, $user)
    {
        if (!password_verify($password, $user->password)) {
            $this->buildErrorResponse(400, 'change-password.WRONG_CURRENT_PASSWORD');
        }
    }

    private function setNewPassword($new_password, $user)
    {
        $user->password = password_hash($new_password, PASSWORD_BCRYPT);
        $this->tryToSaveData($user, 'change-password.PASSWORD_COULD_NOT_BE_UPDATED');
    }

    /**
     * Public functions
     */
    public function index()
    {
        $this->initializeGet();
        $user = $this->findUser($this->decodeToken($this->getToken()))->toArray();
        $user = $this->unsetPropertyFromArray($user, ['password', 'level', 'authorised', 'block_expires', 'login_attempts']);
        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $user);
    }

    public function update()
    {
        $this->initializePatch();
        $this->checkForEmptyData([$this->request->getPut('firstname'), $this->request->getPut('lastname')]);
        $user = $this->updateProfile($this->findUser($this->decodeToken($this->getToken())), $this->request->getPut('firstname'), $this->request->getPut('lastname'), $this->request->getPut('email'), $this->request->getPut('phone'), $this->request->getPut('mobile'), $this->request->getPut('address'), $this->request->getPut('birthday'));
        $user = $user->toArray();
        $user = $this->unsetPropertyFromArray($user, ['password', 'level', 'authorised', 'block_expires', 'login_attempts']);
        $this->registerLog();
        $this->buildSuccessResponse(200, 'profile.PROFILE_UPDATED', $user);
    }

    public function changePassword()
    {
        $this->initializePatch();
        $this->checkForEmptyData([$this->request->getPut('current_password'), $this->request->getPut('new_password')]);
        $user = $this->findUser($this->decodeToken($this->getToken()));
        $this->checkPassword($this->request->getPut('current_password'), $user);
        $this->setNewPassword($this->request->getPut('new_password'), $user);
        $this->registerLog();
        $this->buildSuccessResponse(200, 'change-password.PASSWORD_SUCCESSFULLY_UPDATED');
    }
}
