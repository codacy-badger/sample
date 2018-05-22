<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Resume\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Resume
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Resume_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Resume\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Position is required', $actual['resume_position']);
        $this->assertEquals('File is required', $actual['resume_link']);
    }

    /**
     * @covers Cradle\Module\Resume\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['resume_id']);
    }
}
