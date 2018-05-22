<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Service\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Service
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Service_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Service\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Name is required', $actual['service_name']);
        $this->assertEquals('Credits is required', $actual['service_credits']);
    }

    /**
     * @covers Cradle\Module\Service\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['service_id']);
    }
}
