<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Lead;
use Cradle\Module\Lead\Service as LeadService;
use Cradle\Module\Utility\Validator as UtilityValidator;
/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  lead
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
        if (isset($data['lead_email'])) {
            if (!UtilityValidator::isEmail($data['lead_email'])) {
                $errors['lead_email'] = 'Must be a valid email';
            } else if (LeadService::get('sql')->exists($data['lead_email'])) {
            $errors['lead_email'] = 'Email Address already in use';
            }
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
        if(!isset($data['lead_id']) || !is_numeric($data['lead_id'])) {
            $errors['lead_id'] = 'Invalid ID';
        }

        if (isset($data['lead_email']) && 
            LeadService::get('sql')->exists(
                $data['lead_email'],
                false,
                $data['lead_id'])) {
            $errors['lead_email'] = 'Email is already in use';
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
        // validations
        // required
        if (!isset($data['lead_name']) || empty($data['lead_name'])) {
            $errors['lead_name'] = 'Name is required';
        }
        if (isset($data['lead_image'])
        && ((!empty($data['lead_image']))
        && (!preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['lead_image'])))) {
            $errors['lead_image'] = 'lead_image should be a valid url';
        }

        // if not bulk
        if (!isset($data['bulk_action_active'])  && 
            (!isset($data['lead_email']) || empty($data['lead_email']))) {
            $errors['lead_email'] = 'Email is required';
        }

        if (isset($data['lead_email']) && 
            !UtilityValidator::isEmail($data['lead_email'])) {
            $errors = 'Please use a valid email';
        }
        
        if (isset($data['lead_slug'])
            && !empty($data['lead_slug'])
            && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['lead_slug'])
            ) {
            $errors['lead_slug'] = 'Slug must only have letters, numbers, dashes';
        }
        if (isset($data['lead_detail'])
            && !empty($data['lead_detail'])
            && str_word_count($data['lead_detail']) <= 10) {
            $errors['lead_detail'] = 'Detail should have more than 10 words';
        }
        $choices = array('male', 'female');
        if (isset($data['lead_gender'])
            && !empty($data['lead_gender'])
            && !in_array($data['lead_gender'], $choices) ) {
            $errors['lead_gender'] = 'Should be either male or female';
        }
        // required
        $choices = array('seeker', 'poster');
        if (isset($data['lead_type']) && !in_array($data['lead_type'], $choices)) {
            $errors['lead_type'] = 'Should be either seeker or poster';
        }
        if (isset($data['lead_facebook'])
            && ((!empty($data['lead_facebook']))
            && (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $data['lead_facebook'])))) {
            $errors['lead_facebook'] = 'Lead_facebook must be a valid URL';
        }
        if (isset($data['lead_linkedin'])
            && ((!empty($data['lead_linkedin']))
            && (!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $data['lead_linkedin'])))) {
            $errors['lead_linkedin'] = 'Lead_linkedin must be a valid URL';
        }
        if (isset($data['lead_phone']) && !empty($data['lead_phone']) && !preg_match('/^\d+(-\d+)*$/', $data['lead_phone'])) {
            $errors['lead_phone'] = 'Contact number should be numeric';
        }
        return $errors;
    }
}