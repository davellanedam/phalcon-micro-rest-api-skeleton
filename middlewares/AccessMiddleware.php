<?php

use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * AccessMiddleware
 *
 * Access and user permissions
 */
class AccessMiddleware extends ControllerBase implements MiddlewareInterface
{
    public function call(Phalcon\Mvc\Micro $app)
    {
        // Initialize
        // Gets users ACL
        include APP_PATH . '/config/acl.php';
        $arrHandler = $app->getActiveHandler();
        //get the controller for this handler
        $array = (array) $arrHandler[0];
        $nameController = implode('', $array);
        $controller = str_replace('Controller', '', $nameController);
        // check if controller is Index, if it´s Index, then checks if any of functions are called if so return allow
        if ($controller === 'Index') {
            $allowed = 1;
            return $allowed;
        }

        // gets user token
        $mytoken = $this->getToken();

        // Verifies Token exists and is not empty
        if (empty($mytoken) || $mytoken == '') {
            $this->buildErrorResponse(400, 'common.EMPTY_TOKEN_OR_NOT_RECEIVED');
        }
        // Verifies Token
        try {
            $token_decoded = $this->decodeToken($mytoken);
            // Verifies User role Access
            $allowed_access = $acl->isAllowed($token_decoded->username_level, $controller, $arrHandler[1]);
            return (!$allowed_access) ? $this->buildErrorResponse(403, 'common.YOUR_USER_ROLE_DOES_NOT_HAVE_THIS_FEATURE') : $allowed_access;
        } catch (Exception $e) {
            // BAD TOKEN
            $this->buildErrorResponse(401, 'common.BAD_TOKEN_GET_A_NEW_ONE');
        }
    }
}
