<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Utm;

use Cradle\Module\Utm\Service as UtmService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  utm
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
        if(!isset($data['utm_title']) || empty($data['utm_title'])) {
            $errors['utm_title'] = 'Title is required';
        }
                
        if(!isset($data['utm_campaign']) || empty($data['utm_campaign'])) {
            $errors['utm_campaign'] = 'campaign is required';
        }
                
        if(!isset($data['utm_detail']) || empty($data['utm_detail'])) {
            $errors['utm_detail'] = 'Detail is required';
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
        if(!isset($data['utm_id']) || !is_numeric($data['utm_id'])) {
            $errors['utm_id'] = 'Invalid ID';
        }

        
        if(isset($data['utm_title']) && empty($data['utm_title'])) {
            $errors['utm_title'] = 'Title is required';
        }
                
        if(isset($data['utm_campaign']) && empty($data['utm_campaign'])) {
            $errors['utm_campaign'] = 'campaign is required';
        }
                
        if(isset($data['utm_detail']) && empty($data['utm_detail'])) {
            $errors['utm_detail'] = 'Detail is required';
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
        
        if (isset($data['utm_image']) && !preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['utm_image'])) {
            $errors['utm_image'] = 'Should be a valid url';
        }
                
        return $errors;
    }
}
