<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Feature;

use Cradle\Module\Feature\Service as FeatureService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  feature
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
        if(!isset($data['feature_name']) || empty($data['feature_name'])) {
            $errors['feature_name'] = 'Name is required';
        }
                
        if(!isset($data['feature_title']) || empty($data['feature_title'])) {
            $errors['feature_title'] = 'Title is required';
        }
                
        if(!isset($data['feature_slug']) || empty($data['feature_slug'])) {
            $errors['feature_slug'] = 'Slug is required';
        }
                
        if(!isset($data['feature_detail']) || empty($data['feature_detail'])) {
            $errors['feature_detail'] = 'Detail is required';
        }
        
        if(!isset($data['feature_type']) || empty($data['feature_type'])) {
            $errors['feature_type'] = 'Type is required';
        }
        
        if(!isset($data['feature_image']) || empty($data['feature_image'])) {
            $errors['feature_image'] = 'Image is required';
        }
  
        if(!isset($data['feature_keywords']) || empty($data['feature_keywords'])) {
            $errors['feature_keywords'] = 'Keywords are required';
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
        if(!isset($data['feature_id']) || !is_numeric($data['feature_id'])) {
            $errors['feature_id'] = 'Invalid ID';
        }

        if(isset($data['feature_name']) && empty($data['feature_name'])) {
            $errors['feature_name'] = 'Name is required';
        }
                
        if(isset($data['feature_title']) && empty($data['feature_title'])) {
            $errors['feature_title'] = 'Title is required';
        }
                
        if(isset($data['feature_slug']) && empty($data['feature_slug'])) {
            $errors['feature_slug'] = 'Slug is required';
        }
                
        if(isset($data['feature_detail']) && empty($data['feature_detail'])) {
            $errors['feature_detail'] = 'Detail is required';
        }
        
        if(!isset($data['feature_image']) || empty($data['feature_image'])) {
            $errors['feature_image'] = 'Image is required';
        }
 
        if(!isset($data['feature_keywords']) || empty($data['feature_keywords'])) {
            $errors['feature_keywords'] = 'Keywords are required';
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
        
        $choices = array('position', 'location', 'industry', 'education');
        if (isset($data['feature_type']) && !in_array($data['feature_type'], $choices)) {
            $errors['feature_type'] = 'Must choose a feature type';
        }

        if (isset($data['feature_color']) && !preg_match('#^\#[0-9A-Fa-f]{6}#', $data['feature_color'])) {
            $errors['feature_color'] = 'Must be valid hexadecimal color';
        }

        if (isset($data['feature_subcolor']) && !preg_match('#^\#[0-9A-Fa-f]{6}#', $data['feature_subcolor'])) {
            $errors['feature_subcolor'] = 'Must be valid hexadecimal color';
        }

        if (isset($data['feature_slug']) && !preg_match('#^[A-Z][a-z0-9]+(?:-[A-Z][a-z0-9]+)*$#', $data['feature_slug'])) {
            $errors['feature_slug'] = 'Must be a correct slug format';
        }

        $choices = array('position', 'industry', 'education');
        if (isset($data['feature_type']) && !in_array($data['feature_type'], $choices)) {
            if(!isset($data['feature_map']) || empty($data['feature_map'])) {
                $errors['feature_map'] = 'Map Image is required';
            }     
        }

        return $errors;
    }
}
