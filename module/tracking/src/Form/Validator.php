<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Tracking\Form;

use Cradle\Module\Tracking\Form\Service as FormService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  form
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
        // Checks for missing form_name
        if (!isset($data['form_name']) || !trim($data['form_name'])) {
            $errors['form_name'] = 'Name is required';
        } else if (isset($data['form_name']) && strlen(trim($data['form_name'])) < 2 ) {
            $errors['form_name'] = 'Name must be at least 2 characters long';
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
        if( !isset($data['form_id']) || !is_numeric($data['form_id'])) {
            $errors['form_id'] = 'Invalid ID';
        }

        if (isset($data['form_name']) && trim($data['form_name'])) {
            // Gets the SQL service
            $formSql = FormService::get('sql');

            // Sets the filters
            $search = array();
            $search['exact_filter']['form_name'] = trim($data['form_name']);
            $search['exact_filter']['profile_id'] = $data['profile_id'];
            $search['not_filter']['form_id'] = $data['form_id'];

            // Checks for existing form name
            // Excludes self
            $checker = $formSql->search($search);

            // Checks if a form was returned
            if ($checker['total']) {
                // A form was returned at this point
                // Thus, set error for form_name
                $errors['form_name'] = 'Form name already being used';
            }
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
        // Checks for missing form_name
        if (isset($data['form_name']) && !trim($data['form_name'])) {
            $errors['form_name'] = 'Name is required';
        } else if (isset($data['form_name']) && strlen(trim($data['form_name'])) < 2 ) {
            $errors['form_name'] = 'Name must be at least 2 characters long';
        }
        
        return $errors;
    }
}
