<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Thread\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Thread
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Thread_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Thread\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Gmail thread id is required', $actual['thread_gmail_id']);
        $this->assertEquals('Subject is required', $actual['thread_subject']);
    }

    /**
     * @covers Cradle\Module\Thread\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['thread_id']);
    }
}
