<?php //-->
return function () {
    // add global methods
    $this->package('global')

    /**
     * Check if the role exists on the
     * given request session.
     *
     * @param *string $access
     * @param Request $request
     */
    ->addMethod('role', function ($access, $type, $request) {
        // if session is empty ignore
        if (!isset($_SESSION['me'])) {
            return false;
        }

        // get session
        $session = $_SESSION['me'];

        // if auth id is 1
        if ($session['auth_id'] == 1) {
            return true;
        }

        // if empty permissions
        if (!isset($session['role_permissions']) || empty($session['role_permissions'])) {
            return false;
        }

        // get permissions
        $permissions = $session['role_permissions'];

        // if access is on permissions
        if (in_array($access, $permissions)) {
            return true;
        }

        return false;
    });
};
