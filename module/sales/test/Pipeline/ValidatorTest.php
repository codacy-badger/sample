<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Sales\Pipeline\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Pipeline
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Sales_Pipeline_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Sales\Pipeline\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
    }

    /**
     * @covers Cradle\Module\Sales\Pipeline\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['pipeline_id']);
    }
}
