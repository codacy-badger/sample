<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Crawler\Website;

use Cradle\Module\Crawler\Website\Service as WebsiteService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  website
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
        // website_name - required
        if (!isset($data['website_name']) || empty($data['website_name'])) {
            $errors['website_name'] = 'Cannot be empty';
        }

        // website_root - required
        if (!isset($data['website_root']) || empty($data['website_root'])) {
            $errors['website_root'] = 'Cannot be empty';
        }

        // website_start - required
        if (!isset($data['website_start']) || empty($data['website_start'])) {
            $errors['website_start'] = 'Cannot be empty';
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
        // website_id - required
        if (!isset($data['website_id']) || empty($data['website_id'])) {
            $errors['website_id'] = 'Invalid ID';
        }

        // website_name - required
        if (isset($data['website_name']) && empty($data['website_name'])) {
            $errors['website_name'] = 'Cannot be empty';
        }

        // website_root - required
        if (isset($data['website_root']) && empty($data['website_root'])) {
            $errors['website_root'] = 'Cannot be empty';
        }

        // website_start - required
        if (isset($data['website_start']) && empty($data['website_start'])) {
            $errors['website_start'] = 'Cannot be empty';
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
        return $errors;
    }
}
