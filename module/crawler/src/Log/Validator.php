<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Crawler\Log;

use Cradle\Module\Crawler\Log\Service as LogService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  log
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
        if (!isset($data['log_message']) || empty($data['log_message'])) {
            $errors['log_message'] = 'Message is required';
        }

        if (!isset($data['log_link']) || empty($data['log_link'])) {
            $errors['log_link'] = 'Link is required';
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
        if (!isset($data['log_id']) || !is_numeric($data['log_id'])) {
            $errors['log_id'] = 'Invalid ID';
        }

        if (isset($data['log_message']) && empty($data['log_message'])) {
            $errors['log_message'] = 'Message is required';
        }

        if (isset($data['log_link']) && empty($data['log_link'])) {
            $errors['log_link'] = 'Link is required';
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
