<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Oauth\Auth;

use Cradle\Module\Oauth\Auth\Service;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  Auth
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Validator
{
    /**
     * Returns Create Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getCreateErrors(array $data, array $errors = [])
    {
        //auth_slug        Required
        if (!isset($data['auth_slug']) || empty($data['auth_slug'])) {
            $errors['auth_slug'] = 'Cannot be empty';
        } else if (Service::get('sql')->exists($data['auth_slug'])) {
            $errors['auth_slug'] = 'User Exists';
        }

        // auth_permissions        Required
        if (!isset($data['auth_permissions']) || empty($data['auth_permissions'])) {
            $errors['auth_permissions'] = 'Cannot be empty';
        }

        //auth_password        Required
        if (!isset($data['auth_password']) || empty($data['auth_password'])) {
            $errors['auth_password'] = 'Cannot be empty';
        }

        //confirm        NOT IN SCHEMA
        if (!isset($data['confirm']) || empty($data['confirm'])) {
            $errors['confirm'] = 'Cannot be empty';
        } else if ($data['confirm'] !== $data['auth_password']) {
            $errors['confirm'] = 'Passwords do not match';
        }

        // Checks signup_type
        if (isset($data['signup_type']) && $data['signup_type'] == 'poster') {
            // Checks profile_company
            if (!trim($data['profile_company'])) {
                $errors['profile_company'] = 'Cannot be empty';
            }
        }

        //also add optional errors
        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Login Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getForgotErrors(array $data, array $errors = [])
    {
        //auth_slug        Required
        if (!isset($data['auth_slug']) || empty($data['auth_slug'])) {
            $errors['auth_slug'] = 'Cannot be empty';
        }

        return $errors;
    }

    /**
     * Returns Login Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getLoginErrors(array $data, array $errors = [])
    {
        //auth_slug        Required
        if (!isset($data['auth_slug']) || empty($data['auth_slug'])) {
            $errors['auth_slug'] = 'Cannot be empty';
        } else if (!Service::get('sql')->exists($data['auth_slug'])) {
            $errors['auth_slug'] = 'User does not exist';
        }

        //auth_password        Required
        if (!isset($data['auth_password']) || empty($data['auth_password'])) {
            $errors['auth_password'] = 'Cannot be empty';
        } else if (!isset($errors['auth_slug'])) {
            if (!Service::get('sql')->exists($data['auth_slug'], $data['auth_password'])) {
                $errors['auth_password'] = 'Password is incorrect';
            }
        }

        return $errors;
    }

    /**
     * Returns Login Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getRecoverErrors(array $data, array $errors = [])
    {
        //auth_password        Required
        if (!isset($data['auth_password']) || empty($data['auth_password'])) {
            $errors['auth_password'] = 'Cannot be empty';
        }

        if (isset($data['auth_password']) && strlen($data['auth_password']) < 7) {
            $errors['auth_password'] = 'Must be at least 7 characters long';
        }

        //confirm        NOT IN SCHEMA
        if (!isset($data['confirm']) || empty($data['confirm'])) {
            $errors['confirm'] = 'Cannot be empty';
        } else if ($data['confirm'] !== $data['auth_password']) {
            $errors['confirm'] = 'Passwords do not match';
        }

        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Update Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getUpdateErrors(array $data, array $errors = [])
    {
        // auth_id            Required
        if (!isset($data['auth_id']) || !is_numeric($data['auth_id'])) {
            $errors['auth_id'] = 'Invalid ID';
        }

        //auth_slug        Required
        if (isset($data['auth_slug']) && empty($data['auth_slug'])) {
            $errors['auth_slug'] = 'Cannot be empty, if set';
        //if there are no auth id errors
        } else if (isset($data['auth_slug']) && !isset($errors['auth_id'])) {
            //get the auth that we are updating
            $row = Service::get('sql')->get($data['auth_id']);

            //if the auth slug is changing
            if ($row['auth_slug'] !== $data['auth_slug']) {
                //find the new auth_slug
                $exists = Service::get('sql')->exists($data['auth_slug']);

                //if it is found
                if ($exists) {
                    $errors['auth_slug'] = 'Already Taken';
                }
            }
        }

        // auth_permissions        Required
        if (isset($data['auth_permissions']) && empty($data['auth_permissions'])) {
            $errors['auth_permissions'] = 'Cannot be empty';
        }

        if (isset($data['auth_password']) && empty($data['auth_password'])) {
            $errors['auth_password'] = 'Cannot be empty';
        }

        if (isset($data['confirm']) && empty($data['confirm'])) {
            $errors['confirm'] = 'Cannot be empty';
        }

        //confirm            NOT IN SCHEMA
        if ((
                !empty($data['auth_password']) || !empty($data['confirm'])
            )
            && $data['confirm'] !== $data['auth_password']
        ) {
            $errors['confirm'] = 'Passwords do not match';
        }

        //also add optional errors
        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Login Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getVerifyErrors(array $data, array $errors = [])
    {
        //auth_slug        Required
        if (!isset($data['auth_slug']) || empty($data['auth_slug'])) {
            $errors['auth_slug'] = 'Cannot be empty';
        }

        return $errors;
    }

    /**
     * Returns Optional Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getOptionalErrors(array $data, array $errors = [])
    {
        // auth_flag - small
        if (isset($data['auth_flag']) && !UtilityValidator::isSmall($data['auth_flag'])) {
            $errors['auth_flag'] = 'Should be between 0 and 9';
        }

        //auth_password        7 characters
        if (isset($data['confirm']) && strlen($data['confirm']) < 7) {
            $errors['confirm'] = 'Must be at least 7 characters long';
        }

        //will check chosen password if listed as a commonly used password
        if (isset($data['confirm']) && strlen($data['confirm']) >= 7) {
            $path = cradle('global')->path('config') . '/common_passwords.txt';
            $pwordCheck = file_get_contents($path);

            if (strripos($pwordCheck, $data['confirm'])) {
                $errors['confirm'] = 'Password too common!';
            }
        }

        return $errors;
    }
}
