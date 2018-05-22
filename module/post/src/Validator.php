<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Post;

use Cradle\Module\Post\Service as PostService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  post
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
        // Checks for missing post_name
        if (!isset($data['post_name']) || empty($data['post_name'])) {
            $errors['post_name'] = 'Name is required';
        }
        else if (isset($data['post_name']) && strlen($data['post_name']) < 2 ) {
            $errors['post_name'] = 'Name must be at least 2 characters long';
        }

        // Checks for missing post_position
        if (!isset($data['post_position']) || empty($data['post_position'])) {
            $errors['post_position'] = 'Title is required';
        }
        else if (isset($data['post_position']) && strlen($data['post_position']) < 2 ) {
            $errors['post_position'] = 'Title must be at least 2 characters long';
        }

        // Checks for missing post_location
        if (!isset($data['post_location']) || empty($data['post_location'])) {
            $errors['post_location'] = 'Location is required';
        }
        else if (isset($data['post_location']) && strlen($data['post_location']) < 2 ) {
            $errors['post_location'] = 'Location must be at least 2 characters long';
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
        // Checks for invalid post_id
        if (!isset($data['post_id']) || !is_numeric($data['post_id'])) {
            $errors['post_id'] = 'Invalid ID';
        }

        // Checks for missing post_name
        if (isset($data['post_name']) && empty($data['post_name'])) {
            $errors['post_name'] = 'Name is required';
        }
        else if (isset($data['post_name']) && strlen($data['post_name']) < 2 ) {
            $errors['post_name'] = 'Name must be at least 2 characters long';
        }

        // Checks for missing post_position
        if (isset($data['post_position']) && empty($data['post_position'])) {
            $errors['post_position'] = 'Title is required';
        }
        else if (isset($data['post_position']) && strlen($data['post_position']) < 2 ) {
            $errors['post_position'] = 'Title must be at least 2 characters long';
        }

        // Checks for missing post_location
        if (isset($data['post_location']) && empty($data['post_location'])) {
            $errors['post_location'] = 'Location is required';
        }
        else if (isset($data['post_location']) && strlen($data['post_location']) < 2 ) {
            $errors['post_location'] = 'Location must be at least 2 characters long';
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
        // Checks for post_type
        // Checks of post_type is poster
        if (isset($data['post_type']) && $data['post_type'] == 'poster') {
            // Checks for post_experience
            if (isset($data['post_experience'])) {
                // Checks for non numeric post_experience
                if (!is_numeric($data['post_experience'])) {
                    $errors['post_experience'] = 'Experience should be a number.';
                }

                // Checks for negative post_experience
                if ($data['post_experience'] < 0) {
                    $errors['post_experience'] = 'Experience should not lower than zero.';
                }

                // Checks for post_experience being greater than 60
                if ($data['post_experience'] > 60) {
                    $errors['post_experience'] = 'Experience should not greater than sixty.';
                }
            }
        }

        $choices = array('facebook', 'linkedin');
        if (isset($data['post_verify']) && !in_array($data['post_verify'], $choices)) {
            $errors['post_verify'] = 'Must choose a verification method';
        }

        // Checks for invalid url for post_image
        if (isset($data['post_image']) &&
            !preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['post_image'])) {
            $errors['post_image'] = 'Should be a valid url';
        }

        // Checks for invalid url for post_banner
        if (isset($data['post_banner']) &&
            !preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['post_banner'])) {
            $errors['post_banner'] = 'Should be a valid url';
        }

        // Checks for invalid post_salary_min
        if (isset($data['post_salary_min']) && !preg_match('/^\d+(-\d+)*$/', $data['post_salary_min'])) {
            $errors['post_salary_min'] = 'Must be a valid number';
        }

        // Checks for invalid post_salary_min
        if (isset($data['post_salary_min']) && $data['post_salary_min'] > 10000000) {
            $errors['post_salary_min'] = 'Invalid Salary';
        }

        // Checks for invalid post_salary_max
        if (isset($data['post_salary_max']) && !preg_match('/^\d+(-\d+)*$/', $data['post_salary_max'])) {
            $errors['post_salary_max'] = 'Must be a valid number';
        }

        // Checks for invalid post_salary_min
        if (isset($data['post_salary_max']) && $data['post_salary_max'] > 10000000) {
            $errors['post_salary_max'] = 'Invalid Salary';
        }

        // Checks for invalid salary range
        if (isset($data['post_salary_min']) && isset($data['post_salary_max'])
            && ($data['post_salary_min'] > $data['post_salary_max'])) {
            $errors['post_salary_min'] = 'Value must not be greater than the maximum salary';
        }

        // Checks for invalid url for post_link
        if (isset($data['post_link']) &&
            !preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i',
                $data['post_link'])) {
            $errors['post_link'] = 'Must be a valid URL';
        }

        // Checks for invalid phone for post_phone
        if (isset($data['post_phone']) && !preg_match('/^\d+(\d+)*$/', $data['post_phone']) && !empty($data['post_phone']) ) {
            $errors['post_phone'] = 'Phone number should be numeric';
        }
        else if (isset($data['post_phone']) && !empty($data['post_phone']) && strlen($data['post_phone']) < 7) {
            $errors['post_phone'] = 'Phone number should be at least 7 digits (no alphabets!) or leave blank';
        }

        // Checks for invalid email for post_email
        if (isset($data['post_email']) && filter_var($data['post_email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['post_email'] = 'Should be a valid email';
        }

        // Checks for post_banner & required fields
        if (isset($data['post_name']) && !empty($data['post_name'])
            && isset($data['post_position']) && !empty($data['post_position'])
            && isset($data['post_location']) && !empty($data['post_location'])
            && isset($data['post_email']) && !empty($data['post_email'])
            && isset($data['post_banner']) && !empty($data['post_banner'])) {
            // check if its uploaded on s3
            if (filter_var($data['post_banner'], FILTER_VALIDATE_URL) === FALSE) {
                // get the base
                $base = explode(";", $data['post_banner']);
                // get the image
                $image = explode(":", $base[0]);
                // set the types
                $types = array('image/jpeg', 'image/jpg','image/png');
                // check for valid image
                if (!isset($image[1]) || !in_array($image[1], $types)) {
                    $errors['post_banner'] = 'Invalid Image';
                }
            // check not uploaded on s3 it's base64
            } else {
                // get extension
                $extension = pathinfo($data['post_banner'], PATHINFO_EXTENSION);
                $types = array('jpg', 'jpeg', 'png');

                // Checks the file extension for malformations
                if (strpos($extension, '?') !== false) {
                    $pos = strpos($extension, '?');
                    $extension = substr($extension, 0, $pos);
                }

                // check for valid image
                if (!in_array($extension, $types)) {
                    $errors['post_banner'] = 'Invalid Image';
                }
            }
        }

        // Checks for post_name length
        if (isset($data['post_name']) && strlen($data['post_name']) > 140) {
            $errors['post_name'] = 'Name should not be longer than 140 characters';
        }

        // Checks for post_location length
        if (isset($data['post_location']) && strlen($data['post_location']) > 140) {
            $errors['post_location'] = 'Location should not be longer than 140 characters';
        }

        // Checks for post_position length
        if (isset($data['post_position']) && strlen($data['post_position']) > 140) {
            $errors['post_position'] = 'Title should not be longer than 140 characters';
        }

        // Checks for post_notify
        if (isset($data['post_notify']) && !is_array($data['post_notify'])) {
            $errors['post_notify'] = 'Invalid data for notifications';
        }

        // Checks for post_tags
        if (isset($data['post_tags']) && !is_array($data['post_tags'])) {
            $errors['post_tags'] = 'Invalid data for tags';
        }

        return $errors;
    }
}
