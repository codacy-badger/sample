<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Crawler\Worker;

use Cradle\Module\Crawler\Worker\Service as WorkerService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  worker
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
        if (!isset($data['worker_root']) || empty($data['worker_root'])) {
            $errors['worker_root'] = 'Root is required';
        }

        if (!isset($data['worker_link']) || empty($data['worker_link'])) {
            $errors['worker_link'] = 'Link is required';
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
        if (!isset($data['worker_id']) || !is_numeric($data['worker_id'])) {
            $errors['worker_id'] = 'Invalid ID';
        }

        if (isset($data['worker_root']) && empty($data['worker_root'])) {
            $errors['worker_root'] = 'Root is required';
        }

        if (isset($data['worker_link']) && empty($data['worker_link'])) {
            $errors['worker_link'] = 'Link is required';
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
