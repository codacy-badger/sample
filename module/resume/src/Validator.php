<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Resume;

use Cradle\Module\Resume\Service as ResumeService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  resume
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
        if(!isset($data['resume_position']) || empty($data['resume_position'])) {
            $errors['resume_position'] = 'Position is required';
        }
                
        if(!isset($data['resume_link']) || empty($data['resume_link'])) {
            $errors['resume_link'] = 'File is required';
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
        if(!isset($data['resume_id']) || !is_numeric($data['resume_id'])) {
            $errors['resume_id'] = 'Invalid ID';
        }

        
        if(isset($data['resume_position']) && empty($data['resume_position'])) {
            $errors['resume_position'] = 'Position is required';
        }
                
        if(isset($data['resume_link']) && empty($data['resume_link'])) {
            $errors['resume_link'] = 'File is required';
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
