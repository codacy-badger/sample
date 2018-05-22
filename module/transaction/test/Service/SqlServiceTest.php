<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Transaction\Service;

/**
 * SQL service test
 * Transaction Model Test
 *
 * @vendor   Acme
 * @package  Transaction
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Transaction_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'transaction_status' => 'pending',
            'transaction_payment_method' => 'paypal',
            'transaction_payment_reference' => '123456789',
            'transaction_profile' => '{}',
            'transaction_currency' => 'PHP',
            'transaction_total' => 1000,
            'transaction_credits' => 1000,
            'transaction_statement' => 'Test Transaction'
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['transaction_id']);

        // link transaction to profile
        $this->object->linkProfile($id, 1);
    }

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['transaction_id']);
    }

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['transaction_id']);
    }

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'transaction_id' => $id,
            'transaction_status' => 'pending',
            'transaction_payment_method' => 'paypal',
            'transaction_payment_reference' => '123456789',
            'transaction_profile' => '{}',
            'transaction_currency' => 'PHP',
            'transaction_total' => 2000,
            'transaction_credits' => 2000,
        ]);

        $this->assertEquals($id, $actual['transaction_id']);
    }

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['transaction_id']);
    }

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['transaction_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['transaction_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

}
