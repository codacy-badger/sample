<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Crawler\Webpage;

use Cradle\Module\Crawler\Webpage\Service as WebpageService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  webpage
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
        if (!isset($data['webpage_root']) || empty($data['webpage_root'])) {
            $errors['webpage_root'] = 'Title is required';
        }

        if (!isset($data['webpage_link']) || empty($data['webpage_link'])) {
            $errors['webpage_link'] = 'Link is required';
        }

        if (!isset($data['webpage_type']) || empty($data['webpage_type'])) {
            $errors['webpage_type'] = 'Type is required';
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
        if (!isset($data['webpage_id']) || !is_numeric($data['webpage_id'])) {
            $errors['webpage_id'] = 'Invalid ID';
        }

        if (isset($data['webpage_root']) && empty($data['webpage_root'])) {
            $errors['webpage_root'] = 'Title is required';
        }

        if (isset($data['webpage_link']) && empty($data['webpage_link'])) {
            $errors['webpage_link'] = 'Link is required';
        }

        if (isset($data['webpage_type']) && empty($data['webpage_type'])) {
            $errors['webpage_type'] = 'Type is required';
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

        return $errors;
    }
}
