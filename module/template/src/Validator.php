<?php //-->

/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Template;

use Cradle\Module\Template\Service as TemplateService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  template
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Validator
{
    /**
     * Returns Create Errors
     *
     * @param *array $data
     * @param array $errors
     *
     * @return array
     */
    public static function getCreateErrors(array $data, array $errors = [])
    {
        //template title
        if (!isset($data['template_title']) || empty($data['template_title'])) {
            $errors['template_title'] = 'Invalid Title';
        }

        //template type
        if (!isset($data['template_type']) || empty($data['template_type'])) {
            $errors['template_type'] = 'Invalid Type';
        }

        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Update Errors
     *
     * @param *array $data
     * @param array $errors
     *
     * @return array
     */
    public static function getUpdateErrors(array $data, array $errors = [])
    {
        if (!isset($data['template_id']) || !is_numeric($data['template_id'])) {
            $errors['template_id'] = 'Invalid ID';
        }

        //template title
        if (!isset($data['template_title']) || empty($data['template_title'])) {
            $errors['template_title'] = 'Invalid Title';
        }

        //template type
        if (!isset($data['template_type']) || empty($data['template_type'])) {
            $errors['template_type'] = 'Invalid Type';
        }


        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Optional Errors
     *
     * @param *array $data
     * @param array $errors
     *
     * @return array
     */
    public static function getOptionalErrors(array $data, array $errors = [])
    {
        //validations
        //template type for email
        if (isset($data['template_type'])
            && $data['template_type'] === 'email'
            && empty($data['template_html'])) {
            $errors['template_html'] = 'Html cannot be empty';
        }

        //template type for text
        if (isset($data['template_type'])
            && in_array($data['template_type'], ['sms', 'messenger', 'viber', 'wechat'])
            && empty($data['template_text'])) {
            $errors['template_text'] = 'Text cannot be empty';
        }

        return $errors;
    }
}
