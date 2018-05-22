<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
use Cradle\Module\Post\Validator;
/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Post
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Post_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Post\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Name is required', $actual['post_name']);
        $this->assertEquals('Title is required', $actual['post_position']);
    }
    /**
     * @covers Cradle\Module\Post\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);
        $this->assertEquals('Invalid ID', $actual['post_id']);
    }
}