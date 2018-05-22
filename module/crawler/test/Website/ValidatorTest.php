<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Website\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Website
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Website_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Crawler\Website\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Name is required', $actual['website_name']);
        $this->assertEquals('Root is required', $actual['website_root']);
        $this->assertEquals('Start link is required', $actual['website_start']);
        $this->assertEquals('Currency is required', $actual['website_currency']);
        $this->assertEquals('Locale is required', $actual['website_locale']);
    }

    /**
     * @covers Cradle\Module\Crawler\Website\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['website_id']);
    }
}
