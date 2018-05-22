<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Utility;

/**
 * Validator Helper Methods
 *
 * @vendor   Acme
 * @package  Component
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Validator
{
    /**
     * @var array $currencies
     */
    public static $currencies = [
        'AED', 'AFN', 'ALL', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM',
        'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD',
        'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP',
        'CRC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ETB',
        'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ',
        'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD',
        'IRR', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF',
        'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL',
        'LTL', 'LVL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP',
        'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO',
        'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN',
        'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG',
        'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SVC', 'SYP', 'SZL',
        'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'UAH', 'UGX',
        'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XPF',
        'YER', 'ZAR', 'ZMK', 'ZWL'
    ];

    /**
     * Returns true if the value starts with a specific letter
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function startsWithLetter($value)
    {
        return !!preg_match("/^[a-zA-Z]/i", $value);
    }

    /**
     * Returns true if the value is alpha numeric
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function alphaNumeric($value)
    {
        return !!preg_match('/^[a-zA-Z0-9]+$/', (string) $value);
    }

    /**
     * Returns true if the value is alpha numeric underscore
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function alphaNumericUnderScore($value)
    {
        return !!preg_match('/^[a-zA-Z0-9_]+$/', (string) $value);
    }

    /**
     * Returns true if the value is alpha numeric hyphen
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function alphaNumericHyphen($value)
    {
        return !!preg_match('/^[a-zA-Z0-9-]+$/', (string) $value);
    }

    /**
     * Returns true if the value is alpha numeric hyphen or underscore
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function alphaNumericLine($value)
    {
        return !!preg_match('/^[a-zA-Z0-9-_]+$/', (string) $value);
    }

    /**
     * Test if 0 or 1 or string 1 oe 0
     *
     * @param *scalar $string Value to test
     *
     * @return bool
     */
    public static function isBool($string)
    {
        if (!is_scalar($string) || $string === null) {
            return false;
        }

        $string = (string) $string;

        return $string == '0' || $string == '1';
    }

    /**
     * Returns true if the value is a credit card
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function isCreditCard($value)
    {
        return preg_match('/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]'.
        '{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-'.
        '5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/', $value);
    }

    /**
     * Returns true if the value is a mysql date
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function isDate($value)
    {
        return preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}/is', (string) $value);
    }

    /**
     * Returns true if the value is an email
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function isEmail($value)
    {
        $email = preg_match('/^(?:(?:(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|\x5c(?=[@,"\[\]'.
        '\x5c\x00-\x20\x7f-\xff]))(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]'.
        '\x5c\x00-\x20\x7f-\xff]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff])|\.(?=[^\.])){1,62'.
        '}(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff])|'.
        '[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]{1,2})|"(?:[^"]|(?<=\x5c)"){1,62}")@(?:(?!.{64})'.
        '(?:[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.?|[a-zA-Z0-9]\.?)+\.(?:xn--[a-zA-Z0-9]'.
        '+|[a-zA-Z]{2,20})|\[(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[0-1]?\d?\d|2[0-4]\d|25'.
        '[0-5])){3}\])$/', (string) $value);

        // get the environment
        $environment = cradle()->package('global')->config('settings', 'environment');

        // check for environment
        if (strtolower($environment) == 'production') {
            if ($email && preg_match('/@(mailnator|mailinator)/', $value)) {
                $email = false;
            }
        }

        return $email;
    }

    /**
     * Test if float or string float
     *
     * @param *scalar $number Value to test
     *
     * @return bool
     */
    public static function isFloat($number)
    {
        if (!is_scalar($number) || $number === null) {
            return false;
        }

        $number = (string) $number;

        return preg_match('/^[-+]?(\d*)?\.\d+$/', $number);
    }

    /**
     * Returns true if the value is HTML
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function isHtml($value)
    {
        return preg_match("/<\/?\w+((\s+(\w|\w[\w-]*\w)(\s*=\s*".
        "(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/i", (string) $value);
    }

    /**
     * Test if integer or string integer
     *
     * @param *scalar $number Value to test
     *
     * @return bool
     */
    public static function isInteger($number)
    {
        if (!is_scalar($number) || $number === null) {
            return false;
        }

        $number = (string) $number;

        return preg_match('/^[-+]?\d+$/', $number);
    }

    /**
     * Returns true if the value is JSON
     *
     * @param *scalar $string Value to test
     *
     * @return bool
     */
    public static function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Returns true if the value is between 0 and 9
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function isSmall($value)
    {
        if (!is_scalar($value) || $value === null) {
            return false;
        }

        $value = (float) $value;

        return $value >= 0 && $value <= 9;
    }

    /**
     * Returns true if the value is a mysql time
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function isTime($value)
    {
        return preg_match('/^[0-9]{2}\:[0-9]{2}\:[0-9]{2}$/is', (string) $value);
    }

    /**
     * Returns true if the value is a URL
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function isUrl($value)
    {
        // Checks if the value passed is not a string
        if (!is_string($value)) {
            return 0;
        }

        return preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0'.
        '-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', (string) $value);
    }

    /**
     * Returns true if the value is a hex
     *
     * @param *scalar $value Value to test
     *
     * @return bool
     */
    public static function isHex($value)
    {
        return preg_match("/^[0-9a-fA-F]{6}$/", (string) $value);
    }

    /**
     * Returns Optional Errors
     *
     * @param *array $data
     * @param array $errors
     *
     * @return array
     */
    public static function getFileOptionalErrors(array $data, array $errors = [])
    {
        //validations

        if (isset($data['file_subject']) && empty($data['file_subject'])) {
            $errors['file_subject'] = 'Subject cannot be empty';
        }

        if (isset($data['file_cost']) && !is_numeric($data['file_cost'])) {
            $errors['file_cost'] = 'Must be a number';
        }

        if (isset($data['file_cost'])
            && is_numeric($data['file_cost'])
            && $data['file_cost'] <= 0
        ) {
            $errors['file_cost'] = 'Must be a greater than 0';
        }

        if (isset($data['file_image']) && !preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['file_image'])) {
            $errors['file_image'] = 'Should be a valid url';
        }

        return $errors;
    }

    public static function getFileUploadErrors(array $data, array $errors = [])
    {
        if (isset($data['error']) && $data['error']) {
            $errors['error'] = 'There was an error in the file';
        }

        if (!isset($data['name']) || empty($data['name'])) {
            $errors['error'] = 'File name is invalid';
        }

        if (!isset($data['size']) || empty($data['size']) || $data['size'] < 1) {
            $errors['error'] = 'File size is invalid';
        }

        if (isset($data['size']) && $data['size'] > 25000000) {
            $errors['error'] = 'File size is too large';
        }

        // list of valid file types
        $mimetypes = [
            'pdf',
            'docx',
            'doc',
            'application/pdf',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        // if empty get by file extension
        if (empty($data['type'])) {
            $files = explode('.', $data['name']);
            $data['type'] = end($files);
        }

        if (!in_array($data['type'], $mimetypes)) {
            $errors['error'] = 'File type is not allowed';
        }

        return self::getFileOptionalErrors($data, $errors);
    }

    public static function getSendSesEmailErrors(array $data, array $errors = [])
    {
        $ses = cradle('global')->service('ses');

        if (!$ses) {
            $errors['config'] = 'No ses configurations set';
        }

        //if it's not configured
        if ($ses['token'] === '<AWS TOKEN>'
            || $ses['secret'] === '<AWS SECRET>'
            || $ses['region'] == '<AWS REGION>') {
            $errors['config'] = 'No ses1 configurations set';
        }

        return $errors;
    }

    public static function getSendSmtpEmailErrors(array $data, array $errors = [])
    {
        $swift = cradle('global')->service('mail-main');
        if (!$swift) {
            $errors['config'] = 'No smtp configurations set';
        }

        //if it's not configured
        if ($swift['user'] === '<EMAIL ADDRESS>'
            || $swift['pass'] === '<EMAIL PASSWORD>') {
            $errors['config'] = 'No smtp1 configurations set';
        }

        return $errors;
    }

    public static function getContactEmailErrors(array $data, array $errors = [])
    {
        if (!isset($data['contact_fname']) || empty($data['contact_fname'])) {
            $errors['contact_fname'] = 'Name is required';
        }

        //contact_email        Required
        if (!isset($data['contact_email']) || empty($data['contact_email'])) {
            $errors['contact_email'] = 'Cannot be empty';
        } else if (!self::isEmail($data['contact_email'])) {
            $errors['contact_email'] = 'Invalid email';
        }

        if (!isset($data['contact_message']) || empty($data['contact_message'])) {
            $errors['contact_message'] = 'Message is required';
        }

        // Checks for invalid phone
        if (isset($data['contact_phone']) && !preg_match('/^\d+(-\d+)*$/', $data['contact_phone'])) {
            $errors['contact_phone'] = 'Phone number should be numeric';
        }

        if (!isset($data['contact_subject']) || empty($data['contact_subject'])) {
            $errors['contact_subject'] = 'Please specify subject';
        }

        return $errors;
    }

    /**
     * Returns false if the value is less than
     * or equal the passed argument
     *
     * @param *int $number Number to test against
     * @param *int $value Number to test against
     *
     * @return bool
     */
    public static function lessThanEqualToString($value, $number)
    {
        return $value <= strlen($number);
    }

    /**
     * Compare Date (From - To)
     * or equal the passed argument
     *
     * @param *date $to
     * @param *date $from
     *
     * @return errors
     */
    public static function compareDate($from, $to, array $errors = [])
    {
        if (strtotime($from) == strtotime($to)) {
            $errors['from'] = 'Start date and End date must not have the same date';
        }

        if (strtotime($to) < strtotime($from)) {
            $errors['to'] = 'End date should not be less than from the Start date';
        }

        return $errors;
    }
}
