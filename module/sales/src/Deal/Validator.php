<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Sales\Deal;

use Cradle\Module\Sales\Deal\Service as DealService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  deal
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
        if(!isset($data['deal_id']) || !is_numeric($data['deal_id'])) {
            $errors['deal_id'] = 'Invalid ID';
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

        //deal_amount must be in numbers
        if ((isset($data['deal_amount']) && !is_numeric($data['deal_amount']))
        ) {
            $errors['deal_amount'] = 'Deal amount must be in numbers';
        }

        //deal_status required
        if (!isset($data['deal_status']) || empty($data['deal_status'])) {
            $errors['deal_status'] = 'Deal Status is required';
        }

        //deal close date required
        if ((!isset($data['deal_close']) || empty($data['deal_close'])) &&
            (isset($data['deal_status_update_only']) && ($data['deal_status_update_only']) == 1)
        ) {
            $errors['deal_close'] = 'Deal Close Date is required';
        }

        //pipeline_id must be in numbers
        if ((isset($data['pipeline_id']) && !is_numeric($data['pipeline_id'])) &&
            (isset($data['deal_status_update_only']) && ($data['deal_status_update_only']) == 1)
        ) {
            $errors['pipeline_id'] = 'Pipeline ID must be in numbers';
        }

        //deal_type required
        if ((!isset($data['deal_type']) || empty($data['deal_type'])) &&
            (isset($data['deal_status_update_only']) && ($data['deal_status_update_only']) == 1)
        ) {
            $errors['deal_type'] = 'Deal Type is required';
        }

        //agent_name required
        if ((!isset($data['deal_agent']) || empty($data['deal_agent'])) &&
            (isset($data['deal_status_update_only']) && ($data['deal_status_update_only']) == 1)
        ) {
            $errors['deal_agent'] = 'Agent Name is required';
        }

        //company_name required
        if ((!isset($data['deal_company']) || empty($data['deal_company'])) &&
            (isset($data['deal_status_update_only']) && ($data['deal_status_update_only']) == 1)
        ) {
            $errors['deal_company'] = 'Company Name is required';
        }

        return $errors;
    }
}
