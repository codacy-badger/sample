<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Thread;

use Cradle\Module\Thread\Service as ThreadService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  thread
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
        if(!isset($data['thread_gmail_id']) || empty($data['thread_gmail_id'])) {
            $errors['thread_gmail_id'] = 'Gmail thread id is required';
        }
                
        if(!isset($data['thread_subject']) || empty($data['thread_subject'])) {
            $errors['thread_subject'] = 'Subject is required';
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
        if(!isset($data['thread_id']) || !is_numeric($data['thread_id'])) {
            $errors['thread_id'] = 'Invalid ID';
        }

        
        if(isset($data['thread_gmail_id']) && empty($data['thread_gmail_id'])) {
            $errors['thread_gmail_id'] = 'Gmail thread id is required';
        }
                
        if(isset($data['thread_subject']) && empty($data['thread_subject'])) {
            $errors['thread_subject'] = 'Subject is required';
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
