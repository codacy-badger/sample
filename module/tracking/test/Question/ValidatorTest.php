<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Question\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Question
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Question_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Tracking\Question\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors(['question_name' => '']);

        $this->assertEquals('Question title cannot be empty', $actual['question_name']);
    }

    /**
     * @covers Cradle\Module\Tracking\Question\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['question_id']);
    }
}
