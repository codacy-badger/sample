<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Widget;

use Cradle\Module\Widget\Service as WidgetService;
use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  widget
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
        if(!isset($data['widget_domain']) || empty($data['widget_domain'])) {
            $errors['widget_domain'] = 'Domain is required';
        }

        if(isset($data['widget_domain']) && !UtilityValidator::isUrl($data['widget_domain'])) {
            $errors['widget_domain'] = 'Invalid domain';
        }
      
        if (isset($data['widget_tags']) && empty($data['post_tags']) && $data['widget_type'] == "career_page") {
            $errors['widget_tags'] = 'School is empty!';
        }

        if (!isset($data['widget_tags']) && empty($data['post_tags']) && $data['widget_type'] == "career_page") {
            $errors['widget_tags'] = 'School is required!';
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
        if(!isset($data['widget_id']) || !is_numeric($data['widget_id'])) {
            $errors['widget_id'] = 'Invalid ID';
        }

        if(isset($data['widget_domain']) && empty($data['widget_domain'])) {
            $errors['widget_domain'] = 'Domain is required';
        }

        if(isset($data['widget_domain']) && !UtilityValidator::isUrl($data['widget_domain'])) {
            $errors['widget_domain'] = 'Invalid domain';
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
        if(isset($data['widget_header_color']) 
        && !empty($data['widget_header_color'])
        && !preg_match('/#([a-f0-9]{3}){1,2}\b/i', $data['widget_header_color'])) {
            $errors['widget_header_color'] = 'Invalid widget header color';
        }

        if(isset($data['widget_button_color']) 
        && !empty($data['widget_button_color'])
        && !preg_match('/#([a-f0-9]{3}){1,2}\b/i', $data['widget_button_color'])) {
            $errors['widget_button_color'] = 'Invalid widget button color';
        }
        
        return $errors;
    }
}
