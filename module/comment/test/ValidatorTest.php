<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Comment\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Comment
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Comment_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Comment\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Detail is required', $actual['comment_detail']);
        $this->assertEquals('Title is required', $actual['comment_type']);
    }

    /**
     * @covers Cradle\Module\Comment\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['comment_id']);
    }
}
