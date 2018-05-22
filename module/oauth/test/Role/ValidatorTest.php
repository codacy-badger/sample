<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Oauth\Role\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Oauth_Role_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Oauth\Role\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Role Name is required', $actual['role_name']);
    }

    /**
     * @covers Cradle\Module\Oauth\Role\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['role_id']);
    }
}
