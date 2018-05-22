<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Feature\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Feature
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Feature_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Feature\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Name is required', $actual['feature_name']);
        $this->assertEquals('Title is required', $actual['feature_title']);
        $this->assertEquals('Image is required', $actual['feature_image']);
        $this->assertEquals('Keywords are required', $actual['feature_keywords']);
        $this->assertEquals('Slug is required', $actual['feature_slug']);
        $this->assertEquals('Detail is required', $actual['feature_detail']);
    }

    /**
     * @covers Cradle\Module\Feature\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['feature_id']);
    }
}
