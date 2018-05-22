<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Campaign;

use Cradle\Module\Campaign\Service as CampaignService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  campaign
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
        if(!isset($data['campaign_id']) || !is_numeric($data['campaign_id'])) {
            $errors['campaign_id'] = 'Invalid ID';
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

        if (isset($data['campaign_medium']) && empty($data['campaign_medium'])) {
           $errors['campaign_medium'] = 'Campaign Medium is required';
        }

        if (isset($data['campaign_source']) && empty($data['campaign_source'])) {
           $errors['campaign_source'] = 'Campaign Source is required';
        }

        if (isset($data['campaign_audience']) && empty($data['campaign_audience'])) {
           $errors['campaign_audience'] = 'Campaign Audience is required';
        }

        if (isset($data['template_id']) && empty($data['template_id'])) {
           $errors['template_id'] = 'Template ID is required';
       }

        return $errors;
    }
}
