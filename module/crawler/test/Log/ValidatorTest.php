<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Log\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Log
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Log_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Crawler\Log\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Message is required', $actual['log_message']);
        $this->assertEquals('Link is required', $actual['log_link']);
    }

    /**
     * @covers Cradle\Module\Crawler\Log\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['log_id']);
    }
}
