<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Position;

use Cradle\Module\Position\Service as PositionService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  position
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
        if(!isset($data['position_name']) || empty($data['position_name'])) {
            $errors['position_name'] = 'Name is required';
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
        if(!isset($data['position_id']) || !is_numeric($data['position_id'])) {
            $errors['position_id'] = 'Invalid ID';
        }

        if(isset($data['position_name']) && empty($data['position_name'])) {
            $errors['position_name'] = 'Name is required';
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
        // if(!isset($data['position_description']) || empty($data['position_description'])) {
        //     $errors['position_description'] = 'Detail is required';
        // }

        return $errors;
    }
}
