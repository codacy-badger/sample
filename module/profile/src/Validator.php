<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Profile;

use Cradle\Module\Profile\Service as ProfileService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  profile
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
        if (!isset($data['profile_name']) || empty($data['profile_name'])) {
            $errors['profile_name'] = 'Name is required';
        }

        //profile_email        Required
        if (!isset($data['profile_email']) || empty($data['profile_email'])) {
            $errors['profile_email'] = 'Cannot be empty';
        } else if (ProfileService::get('sql')->exists($data['profile_email'])) {
            $errors['profile_email'] = 'User Exists';
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
        if (!isset($data['profile_id']) || !is_numeric($data['profile_id'])) {
            $errors['profile_id'] = 'Invalid ID';
        }

        if (isset($data['profile_parent']) && !is_numeric($data['profile_parent'])) {
            $errors['profile_parent'] = 'Invalid ID';
        }

        if (isset($data['profile_name']) && empty($data['profile_name'])) {
            $errors['profile_name'] = 'Name is required';
        }

        return self::getOptionalErrors($data, $errors);
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
        //validations

        if (isset($data['profile_image']) && !preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['profile_image'])) {
            $errors['profile_image'] = 'Should be a valid url';
        }

        if (isset($data['profile_email']) && !UtilityValidator::isEmail($data['profile_email'])) {
            $errors['profile_email'] = 'Must be a valid email';
        }

        if (isset($data['profile_slug']) && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['profile_slug'])) {
            $errors['profile_slug'] = 'Slug must only have letters, numbers, dashes';
        }

        if (isset($data['profile_detail']) && str_word_count($data['profile_detail']) <= 10) {
            $errors['profile_detail'] = 'Detail should have more than 10 words';
        }

        $choices = array('male', 'female');
        if (isset($data['profile_gender']) && !in_array($data['profile_gender'], $choices)) {
            $errors['profile_gender'] = 'Should be either male or female';
        }

        if (isset($data['profile_website']) && ((!empty($data['profile_website'])) && (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $data['profile_website'])))) {
            $errors['profile_website'] = 'Must be a valid URL';
        }

        if (isset($data['profile_facebook']) && ((!empty($data['profile_facebook'])) && (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $data['profile_facebook'])))) {
            $errors['profile_facebook'] = 'Must be a valid URL';
        }

        if (isset($data['profile_linkedin']) && ((!empty($data['profile_linkedin'])) && (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $data['profile_linkedin'])))) {
            $errors['profile_linkedin'] = 'Must be a valid URL';
        }

        if (isset($data['profile_twitter']) && ((!empty($data['profile_twitter'])) && (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $data['profile_twitter'])))) {
            $errors['profile_twitter'] = 'Must be a valid URL';
        }

        if (isset($data['profile_google']) && ((!empty($data['profile_google'])) && (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $data['profile_google'])))) {
            $errors['profile_google'] = 'Must be a valid URL';
        }

        if (isset($data['profile_phone']) && !preg_match('/^\d+(-\d+)*$/', $data['profile_phone']) && !empty($data['profile_phone'])) {
            $errors['profile_phone'] = 'Contact number should be numeric';
        }

        if (isset($data['profile_address_postal']) && !empty($data['profile_address_postal'])) {
            if ($data['profile_address_postal'] === '00000') {
                $errors['profile_address_postal'] = 'Please enter a valid postal code';
            }
        }

        if (isset($data['profile_address_city']) && empty($data['profile_address_city'])) {
            $errors['profile_address_city'] = 'This is a required field';
        }
        
        if (isset($data['profile_address_state']) && empty($data['profile_address_state'])) {
            $errors['profile_address_state'] = 'This is a required field';
        }

        return $errors;
    }
}
