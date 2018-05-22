<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Term\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Term
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Term_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Term\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Name is required', $actual['term_name']);
    }

    /**
     * @covers Cradle\Module\Term\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['term_id']);
    }
}
