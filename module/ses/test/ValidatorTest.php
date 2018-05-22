<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ses\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Ses
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ses_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Ses\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
    }

    /**
     * @covers Cradle\Module\Ses\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['ses_id']);
    }
}
