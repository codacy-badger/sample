<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Webpage\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Webpage
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Webpage_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Crawler\Webpage\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Title is required', $actual['webpage_root']);
        $this->assertEquals('Link is required', $actual['webpage_link']);
        $this->assertEquals('Type is required', $actual['webpage_type']);
    }

    /**
     * @covers Cradle\Module\Crawler\Webpage\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['webpage_id']);
    }
}
