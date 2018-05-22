<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Service;

use Cradle\Module\Service\Service as ServiceService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  service
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
        if(!isset($data['service_name']) || empty($data['service_name'])) {
            $errors['service_name'] = 'Name is required';
        }
                
        if(!isset($data['service_credits']) || empty($data['service_credits'])) {
            $errors['service_credits'] = 'Credits is required';
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
        if(!isset($data['service_id']) || !is_numeric($data['service_id'])) {
            $errors['service_id'] = 'Invalid ID';
        }

        
        if(isset($data['service_name']) && empty($data['service_name'])) {
            $errors['service_name'] = 'Name is required';
        }
                
        if(isset($data['service_credits']) && empty($data['service_credits'])) {
            $errors['service_credits'] = 'Credits is required';
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
        
        if (isset($data['service_flag']) && !is_numeric($data['service_flag'])) {
            $errors['service_flag'] = 'Must be a number';
        }
                
        if(isset($data['service_flag'])
            && is_numeric($data['service_flag'])
            && $data['service_flag'] <= -1
        )
        {
            $errors['service_flag'] = 'Must be between 0 and 9';
        }
                
        if(isset(
            $data['service_flag'])
            && is_numeric($data['service_flag'])
            && $data['service_flag'] >= 10
        )
        {
            $errors['service_flag'] = 'Must be between 0 and 9';
        }
                
        return $errors;
    }
}
