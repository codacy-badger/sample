<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Comment;

use Cradle\Module\Comment\Service as CommentService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  comment
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
        if(!isset($data['comment_detail']) || empty($data['comment_detail'])) {
            $errors['comment_detail'] = 'Detail is required';
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
        if(!isset($data['comment_id']) || !is_numeric($data['comment_id'])) {
            $errors['comment_id'] = 'Invalid ID';
        }


        if(isset($data['comment_detail']) && empty($data['comment_detail'])) {
            $errors['comment_detail'] = 'Detail is required';
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

        return $errors;
    }
}
