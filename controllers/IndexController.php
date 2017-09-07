<?php

class indexController extends ControllerBase
{
    /**
     * Gets view
     */
    public function index()
    {
        echo $this->view->render('index');
    }

    /**
     * Login process
     */
    public function login()
    {

        // Verifies if is post request
        $this->initializePost();

        $headers = $this->request->getHeaders();
        if (!isset($headers["Authorization"]) || empty($headers["Authorization"])){
            //checks if Authorization header exists and it´s not empty
            $this->buildErrorResponse(403, "common.HEADER_AUTHORIZATION_NOT_SENT");
        } else {
            // gets credentials
            $credentials = $this->request->getBasicAuth();

            $username = $credentials['username'];

            //gets headers
            // do query
            $conditions = "username = :username:";
            $parameters = array(
                "username" => $username
            );
            $user = Users::findFirst(
                array(
                    $conditions,
                    "bind" => $parameters
                )
            );
            // User exists?
            if (!$user) {
                $this->buildErrorResponse(404, "login.USER_IS_NOT_REGISTERED");
            } else {
                // check if user is blocked
                $block_expires = strtotime($user->block_expires);
                $now = $this->getNowDateTime();
                if ($block_expires > $now) {
                    $this->buildErrorResponse(403, "login.USER_BLOCKED");
                } else if ($user->authorised == 0) {
                    // is user authorized?
                    $this->buildErrorResponse(403, "login.USER_UNAUTHORIZED");
                } else if (!password_verify($credentials['password'], $user->password)) {
                    // Check if passwords do not match
                    // adds +1 to login_attempts
                    $user->login_attempts = $user->login_attempts + 1;
                    if (!$user->save()) {
                        // Send errors
                        $errors = array();
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $this->buildErrorResponse(400, "common.THERE_HAS_BEEN_AN_ERROR", $errors);
                    } else {
                        // checks if login_attempts are less or equal to 4
                        if ($user->login_attempts <= 4) {
                            $this->buildErrorResponse(400, "login.WRONG_USER_PASSWORD");
                        } else {
                            // adds 120 mins block
                            $block_date = $this->getNowDateTimePlusMinutes(120);
                            $user->block_expires = $block_date;
                            if (!$user->save()) {
                                // Send errors
                                $errors = array();
                                foreach ($user->getMessages() as $message) {
                                    $errors[] = $message->getMessage();
                                }
                                $this->buildErrorResponse(400, "common.THERE_HAS_BEEN_AN_ERROR", $errors);
                            } else {
                                $this->buildErrorResponse(400, "login.TOO_MANY_FAILED_LOGIN_ATTEMPTS");
                            }
                        }
                    }
                } else {
                    // ALL OK, proceed to login
                    // options for hash
                    $options = [
                        'cost' => 10, // the default cost is 10, max is 12.
                    ];
                    // Check if password needs rehash
                    if (password_needs_rehash($user->password, PASSWORD_DEFAULT, $options)) {
                        // if so, create new hash and replace old one
                        $newHash = password_hash($credentials['password'], PASSWORD_DEFAULT, $options);
                        // Updates password with new hash in DB for the user
                        $user->password = $newHash;
                        if (!$user->save()) {
                            // Send errors
                            $errors = array();
                            foreach ($user->getMessages() as $message) {
                                $errors[] = $message->getMessage();
                            }
                            $this->buildErrorResponse(400, "common.THERE_HAS_BEEN_AN_ERROR", $errors);
                        }
                    }
                    // Save user data in array
                    $user_data = array(
                        "id" => $user->id,
                        "username" => $user->username,
                        "email" => $user->email,
                        "firstname" => $user->firstname,
                        "lastname" => $user->lastname,
                    );

                    // issue at time and expires (token)
                    $iat = strtotime($this->getNowDateTime());
                    $exp = strtotime("+" . $this->tokenConfig['expiration_time'] . " seconds", $iat);

                    $token_data = array(
                        "iss" => $this->tokenConfig['iss'],
                        "aud" => $this->tokenConfig['aud'],
                        "iat" => $iat,
                        "exp" => $exp,
                        "username_username" => $user->username,
                        "username_firstname" => $user->firstname,
                        "username_lastname" => $user->lastname,
                        "username_level" => $user->level,
                        "rand" => rand().microtime()
                    );

                    // Encode token
                    $token = $this->encodeToken($token_data);

                    $data = array(
                        "token" => $token,
                        "user" => $user_data
                    );
                    // Resets login_attempts
                    $user->login_attempts = 0;
                    // Saves user info in db
                    if (!$user->save()) {
                        // Send errors
                        $errors = array();
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $this->buildErrorResponse(400, "common.THERE_HAS_BEEN_AN_ERROR", $errors);
                    } else {
                        // Registers new user access
                        $newAccess = new UsersAccess();
                        $newAccess->username = $user->username;
                        if (isset($headers["Http-Client-Ip"]) || !empty($headers["Http-Client-Ip"])){
                            $newAccess->ip = $headers["Http-Client-Ip"];
                        } else {
                            $newAccess->ip = $this->request->getClientAddress();
                        }
                        if (isset($headers["Http-Client-Domain"]) || !empty($headers["Http-Client-Domain"])){
                            $newAccess->domain = $headers["Http-Client-Domain"];
                        } else {
                            $newAccess->domain = gethostbyaddr($this->request->getClientAddress());
                        }
                        // Gets country from CloudFlare (if you use it)
                        if (isset($headers["Http-Client-Country"]) || !empty($headers["Http-Client-Country"])){
                            $newAccess->country = $headers["Http-Client-Country"];
                        } else {
                            if (isset($_SERVER["HTTP_CF_IPCOUNTRY"])) {
                                $newAccess->country = $_SERVER["HTTP_CF_IPCOUNTRY"];
                            } else {
                                $newAccess->country = "XX";
                            }
                        }
                        $newAccess->browser = $this->request->getUserAgent();
                        $newAccess->date = $this->getNowDateTime();
                        if (!$newAccess->save()) {
                            // Send errors
                            $errors = array();
                            foreach ($newAccess->getMessages() as $message) {
                                $errors[] = $message->getMessage();
                            }
                            $this->buildErrorResponse(400, "common.THERE_HAS_BEEN_AN_ERROR", $errors);
                        } else {
                            //return 200, ALL OK LOGGED IN
                            $this->buildSuccessResponse(200, "common.SUCCESSFUL_REQUEST", $data);
                        }
                    }
                }
            }
        }
    }
}
