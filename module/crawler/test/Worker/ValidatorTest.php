<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Worker\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Worker
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Worker_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Crawler\Worker\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Root is required', $actual['worker_root']);
        $this->assertEquals('Link is required', $actual['worker_link']);
    }

    /**
     * @covers Cradle\Module\Crawler\Worker\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['worker_id']);
    }
}
