<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Term;

use Cradle\Module\Term\Service as TermService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  term
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
        if(!isset($data['term_name']) || empty($data['term_name'])) {
            $errors['term_name'] = 'Name is required';
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
        if(!isset($data['term_id']) || !is_numeric($data['term_id'])) {
            $errors['term_id'] = 'Invalid ID';
        }

        
        if(isset($data['term_name']) && empty($data['term_name'])) {
            $errors['term_name'] = 'Name is required';
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
        
        if (isset($data['term_hits']) && !is_numeric($data['term_hits'])) {
            $errors['term_hits'] = 'Experience should be a number.';
        }
                
        return $errors;
    }
}
