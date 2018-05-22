<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Transaction;

use Cradle\Module\Transaction\Service as TransactionService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  transaction
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
        if(!isset($data['transaction_status']) || empty($data['transaction_status'])) {
            $errors['transaction_status'] = 'Status is required';
        }
                
        if(!isset($data['transaction_payment_method']) || empty($data['transaction_payment_method'])) {
            $errors['transaction_payment_method'] = 'Payment method is required';
        }
                
        if(!isset($data['transaction_payment_reference']) || empty($data['transaction_payment_reference'])) {
            $errors['transaction_payment_reference'] = 'Payment reference is required';
        }
                
        if(!isset($data['transaction_profile']) || empty($data['transaction_profile'])) {
            $errors['transaction_profile'] = 'Profile information is required';
        }
                
        if(!isset($data['transaction_currency']) || empty($data['transaction_currency'])) {
            $errors['transaction_currency'] = 'Currency is required';
        }
                
        if(!isset($data['transaction_total']) || empty($data['transaction_total'])) {
            $errors['transaction_total'] = 'Total is required';
        }
                
        if(!isset($data['transaction_credits']) || empty($data['transaction_credits'])) {
            $errors['transaction_credits'] = 'Credits is required';
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
        if(!isset($data['transaction_id']) || !is_numeric($data['transaction_id'])) {
            $errors['transaction_id'] = 'Invalid ID';
        }

        
        if(isset($data['transaction_status']) && empty($data['transaction_status'])) {
            $errors['transaction_status'] = 'Status is required';
        }
                
        if(isset($data['transaction_payment_method']) && empty($data['transaction_payment_method'])) {
            $errors['transaction_payment_method'] = 'Payment method is required';
        }
                
        if(isset($data['transaction_payment_reference']) && empty($data['transaction_payment_reference'])) {
            $errors['transaction_payment_reference'] = 'Payment reference is required';
        }
                
        if(isset($data['transaction_profile']) && empty($data['transaction_profile'])) {
            $errors['transaction_profile'] = 'Profile information is required';
        }
                
        if(isset($data['transaction_currency']) && empty($data['transaction_currency'])) {
            $errors['transaction_currency'] = 'Currency is required';
        }
                
        if(isset($data['transaction_total']) && empty($data['transaction_total'])) {
            $errors['transaction_total'] = 'Total is required';
        }
                
        if(isset($data['transaction_credits']) && empty($data['transaction_credits'])) {
            $errors['transaction_credits'] = 'Credits is required';
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
        
        $choices = array('pending', 'complete', 'verified', 'match', 'rejected', 'refunded');
        if (isset($data['transaction_status']) && !in_array($data['transaction_status'], $choices)) {
            $errors['transaction_status'] = 'Invalid status';
        }
                
        if (isset($data['transaction_flag']) && !is_numeric($data['transaction_flag'])) {
            $errors['transaction_flag'] = 'Must be a number';
        }
                
        if(isset($data['transaction_flag'])
            && is_numeric($data['transaction_flag'])
            && $data['transaction_flag'] <= -1
        )
        {
            $errors['transaction_flag'] = 'Must be between 0 and 9';
        }
                
        if(isset(
            $data['transaction_flag'])
            && is_numeric($data['transaction_flag'])
            && $data['transaction_flag'] >= 10
        )
        {
            $errors['transaction_flag'] = 'Must be between 0 and 9';
        }
                
        return $errors;
    }
}
