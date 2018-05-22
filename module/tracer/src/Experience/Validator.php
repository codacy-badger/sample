<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Tracer\Experience;

use Cradle\Module\Tracer\Experience\Service as ExperienceService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  experience
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
        if(!isset($data['experience_id']) || !is_numeric($data['experience_id'])) {
            $errors['experience_id'] = 'Invalid ID';
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
        
        $choices = array('yes', 'no');
        if (isset($data['experience_related']) && !in_array($data['experience_related'], $choices)) {
            $errors['experience_related'] = 'Should be either yes or no';
        }
                
        return $errors;
    }
}
