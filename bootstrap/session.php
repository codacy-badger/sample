<?php //-->
return function ($request, $response) {
    //prevent starting session in cli mode
    if (php_sapi_name() === 'cli') {
        return;
    }

    //start session
    session_start();

    //sync the session
    $request->setSession($_SESSION);

    //set the language
    if (!$request->hasSession('i18n')) {
        $request->setSession('i18n', 'en_US');
        $settings = $this->package('global')->config('settings');
        if (isset($settings['i18n'])) {
            $request->setSession('i18n', $settings['i18n']);
        }
    }

    //create some global methods
    $this->package('global')

    /**
     * Short Hand Redirect
     *
     * @param *string $path
     */
    ->addMethod('redirect', function ($path, $redirectCode = 0) {
        if (!$redirectCode) {
            cradle()->getDispatcher()->redirect($path);
        } else {
            header('Location: ' . $path, true, $redirectCode);
            exit();
        }
    })

    /**
     * Check Session Me
     *
     * @param *string $type
     */
    ->addMethod('requireLogin', function ($type = null) {
        if (!isset($_SESSION['me']['auth_id'])) {
            $redirect = urlencode($_SERVER['REQUEST_URI']);
            return cradle()->getDispatcher()->redirect('/login?redirect_uri=' . $redirect);
        }

        if ($type && $_SESSION['me']['auth_type'] !== $type) {
            cradle('global')->flash('Unauthorized', 'danger');
            return cradle()->getDispatcher()->redirect('/');
        }
    })

    /**
     * Check Session Me Package profile_package
     *
     * @param *string $package
     */
    ->addMethod('requireProfilePackage', function ($package) {
        // Checks if there is no package or if the package is empty
        if (!isset($_SESSION['me']['profile_package'])
            || empty($_SESSION['me']['profile_package'])) {
            return false;
        }

        // Checks if the package needed exists
        if (!in_array($package, $_SESSION['me']['profile_package'])) {
            return false;
        }

        return true;
    })

    /**
     * Check if route is for poster/seeker
     *
     * @param *string $package
     */
    ->addMethod('checkProfile', function ($type) {
        // Checks if the user is not logged in
        if (!isset($_SESSION['me'])) {
            cradle('global')->flash('Unauthorized', 'danger');
            return cradle('global')->redirect('/');
        }

        $company = $_SESSION['me']['profile_company'];

        // check if seeker
        if ($type == 'seeker' && $company) {
            cradle('global')->flash('Unauthorized', 'danger');
            return cradle('global')->redirect('/');
        }

        // check if poster
        if ($type == 'poster' && !$company) {
            cradle('global')->flash('Unauthorized', 'danger');
            return cradle('global')->redirect('/');
        }

        return true;
    })

    /**
     * Short Hand Redirect
     *
     * @param *string $path
     */
    ->addMethod('requireRestLogin', function ($app) {
        if (!isset($_SESSION['rest'])) {
            $redirect = urlencode($_SERVER['REQUEST_URI']);
            return cradle('global')->redirect('/control/'.$app.'/login?redirect_uri=' . $redirect);
        }
    })

    /**
     * Short Hand Redirect
     *
     * @param *string $path
     */
    ->addMethod('flash', function ($message, $type = 'info', $timeout = 3000) {
        $_SESSION['flash'] = [
            'message' => cradle()->package('global')->translate($message),
            'type' => $type,
            'timeout' => $timeout
        ];
    })

    /**
     * Short Hand Redirect
     *
     * @param *string $path
     */
    ->addMethod('setBadge', function ($image, $action) {
        $_SESSION['badge'] = [
            'image' => $image,
            'action' => $action
        ];
    })

    /**
     * Short Hand Redirect
     *
     * @param *string $path
     */
    ->addMethod('setLoggedInBadge', function ($image, $action) {
        $_SESSION['logged_in_badge'] = [
            'image' => $image,
            'action' => $action
        ];
    })

    /**
     * Short Hand Redirect
     *
     * @param *string $path
     */
    ->addMethod('setExperienceFlash', function ($message) {
        $_SESSION['exp'][] = $message;
    })

    /**
     * Short Hand Redirect
     *
     * @param *string $path
     */
    ->addMethod('setRank', function ($image, $level, $title, $credits, $action) {
        $_SESSION['rank'] = [
            'image' => $image,
            'level' => $level,
            'title' => $title,
            'credits' => $credits,
            'action' => $action
        ];
    });
};
