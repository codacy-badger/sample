<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Tracking\Question;

use Cradle\Module\Tracking\Question\Service as QuestionService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  question
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
        if (!trim($data['question_name'])) {
            $errors['question_name'] = 'Question title cannot be empty';
        }

        if (empty($data['question_choices']) && !isset($data['question_custom'])
            && !isset($data['question_file'])) {
            $errors['question_answer'] = 'Question has no answers';
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
        if(!isset($data['question_id']) || !is_numeric($data['question_id'])) {
            $errors['question_id'] = 'Invalid ID';
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
