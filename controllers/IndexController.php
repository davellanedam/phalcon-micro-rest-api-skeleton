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
     * Private functions
     */
    private function checkIfHeadersExist($headers)
    {
        return (!isset($headers['Authorization']) || empty($headers['Authorization'])) ? $this->buildErrorResponse(403, 'common.HEADER_AUTHORIZATION_NOT_SENT') : true;
    }

    private function findUser($credentials)
    {
        $username = $credentials['username'];

        $conditions = 'username = :username:';
        $parameters = array(
            'username' => $username,
        );
        $user = Users::findFirst(
            array(
                $conditions,
                'bind' => $parameters,
            )
        );
        return (!$user) ? $this->buildErrorResponse(404, 'login.USER_IS_NOT_REGISTERED') : $user;
    }

    private function getUserPassword($credentials)
    {
        return $credentials['password'];
    }

    private function checkIfUserIsNotBlocked($user)
    {
        $block_expires = strtotime($user->block_expires);
        $now = strtotime($this->getNowDateTime());
        return ($block_expires > $now) ? $this->buildErrorResponse(403, 'login.USER_BLOCKED') : true;
    }

    private function checkIfUserIsAuthorized($user)
    {
        return ($user->authorised == 0) ? $this->buildErrorResponse(403, 'login.USER_UNAUTHORIZED') : true;
    }

    private function addOneLoginAttempt($user)
    {
        $user->login_attempts = $user->login_attempts + 1;
        $this->tryToSaveData($user);
        return $user->login_attempts;
    }

    private function addXMinutesBlockToUser($minutes, $user)
    {
        $user->block_expires = $this->getNowDateTimePlusMinutes($minutes);
        if ($this->tryToSaveData($user)) {
            $this->buildErrorResponse(400, 'login.TOO_MANY_FAILED_LOGIN_ATTEMPTS');
        }
    }

    private function checkPassword($password, $user)
    {
        if (!password_verify($password, $user->password)) {
            $login_attempts = $this->addOneLoginAttempt($user);
            ($login_attempts <= 4) ? $this->buildErrorResponse(400, 'login.WRONG_USER_PASSWORD') : $this->addXMinutesBlockToUser(120, $user);
        }
    }

    private function checkIfPasswordNeedsRehash($password, $user)
    {
        $options = [
            'cost' => 10, // the default cost is 10, max is 12.
        ];
        if (password_needs_rehash($user->password, PASSWORD_DEFAULT, $options)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT, $options);
            $user->password = $newHash;
            $this->tryToSaveData($user);
        }
    }

    private function buildUserData($user)
    {
        $user_data = array(
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
        );
        return $user_data;
    }

    private function buildTokenData($user)
    {
        // issue at time and expires (token)
        $iat = strtotime($this->getNowDateTime());
        $exp = strtotime('+' . $this->tokenConfig['expiration_time'] . ' seconds', $iat);

        $token_data = array(
            'iss' => $this->tokenConfig['iss'],
            'aud' => $this->tokenConfig['aud'],
            'iat' => $iat,
            'exp' => $exp,
            'username_username' => $user->username,
            'username_firstname' => $user->firstname,
            'username_lastname' => $user->lastname,
            'username_level' => $user->level,
            'rand' => rand() . microtime(),
        );
        return $token_data;
    }

    private function resetLoginAttempts($user)
    {
        $user->login_attempts = 0;
        $this->tryToSaveData($user);
    }

    private function registerNewUserAccess($user)
    {
        $headers = $this->request->getHeaders();
        $newAccess = new UsersAccess();
        $newAccess->username = $user->username;
        $newAccess->ip = (isset($headers['Http-Client-Ip']) || !empty($headers['Http-Client-Ip'])) ? $headers['Http-Client-Ip'] : $this->request->getClientAddress();
        $newAccess->domain = (isset($headers['Http-Client-Domain']) || !empty($headers['Http-Client-Domain'])) ? $headers['Http-Client-Domain'] : gethostbyaddr($this->request->getClientAddress());
        $newAccess->country = (isset($headers['Http-Client-Country']) || !empty($headers['Http-Client-Country'])) ? $headers['Http-Client-Country'] : ($this->request->getServer('HTTP_CF_IPCOUNTRY') !== null) ? $this->request->getServer('HTTP_CF_IPCOUNTRY') : 'XX';
        $newAccess->browser = $this->request->getUserAgent();
        $newAccess->date = $this->getNowDateTime();
        $this->tryToSaveData($newAccess);
    }

    /**
     * Public functions
     */
    public function login()
    {
        $this->initializePost($this->request->getHeaders());
        if ($this->checkIfHeadersExist($this->request->getHeaders())) {
            $user = $this->findUser($this->request->getBasicAuth());
            $password = $this->getUserPassword($this->request->getBasicAuth());
            $this->checkIfUserIsNotBlocked($user);
            $this->checkIfUserIsAuthorized($user);
            $this->checkPassword($password, $user);

            // ALL OK, proceed to login
            $this->checkIfPasswordNeedsRehash($password, $user);
            $user_data = $this->buildUserData($user);
            $token = $this->encodeToken($this->buildTokenData($user));

            $data = array(
                'token' => $token,
                'user' => $user_data,
            );

            $this->resetLoginAttempts($user);
            $this->registerNewUserAccess($user);
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
        }
    }
}
