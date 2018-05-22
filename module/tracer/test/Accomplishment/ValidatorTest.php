<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracer\Accomplishment\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Accomplishment
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracer_Accomplishment_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Tracer\Accomplishment\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
    }

    /**
     * @covers Cradle\Module\Tracer\Accomplishment\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['accomplishment_id']);
    }
}
