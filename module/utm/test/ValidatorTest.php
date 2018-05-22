<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utm\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Utm
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Utm_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Utm\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Title is required', $actual['utm_title']);
        $this->assertEquals('campaign is required', $actual['utm_campaign']);
        $this->assertEquals('Detail is required', $actual['utm_detail']);
    }

    /**
     * @covers Cradle\Module\Utm\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['utm_id']);
    }
}
