<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Transaction\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Transaction
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Transaction_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Transaction\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Status is required', $actual['transaction_status']);
        $this->assertEquals('Payment method is required', $actual['transaction_payment_method']);
        $this->assertEquals('Payment reference is required', $actual['transaction_payment_reference']);
        $this->assertEquals('Profile information is required', $actual['transaction_profile']);
        $this->assertEquals('Currency is required', $actual['transaction_currency']);
        $this->assertEquals('Total is required', $actual['transaction_total']);
        $this->assertEquals('Credits is required', $actual['transaction_credits']);
    }

    /**
     * @covers Cradle\Module\Transaction\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['transaction_id']);
    }
}
