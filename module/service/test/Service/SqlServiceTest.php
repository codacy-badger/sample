<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Service\Service;

/**
 * SQL service test
 * Service Model Test
 *
 * @vendor   Acme
 * @package  Service
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Service_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Service\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Service\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'service_name' => 'A Service',
            'service_credits' => 3,
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['service_id']);
    }

    /**
     * @covers Cradle\Module\Service\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['service_id']);
    }

    /**
     * @covers Cradle\Module\Service\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['service_id']);
    }

    /**
     * @covers Cradle\Module\Service\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'service_id' => $id,
            'service_name' => 'A Service',
            'service_credits' => 5,
        ]);

        $this->assertEquals($id, $actual['service_id']);
    }

    /**
     * @covers Cradle\Module\Service\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['service_id']);
    }

    /**
     * @covers Cradle\Module\Service\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['service_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Service\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['service_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

}
