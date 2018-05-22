<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Event\Service;

/**
 * SQL service test
 * Event Model Test
 *
 * @vendor   Acme
 * @package  Event
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Event_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Event\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Event\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['event_id']);
    }

    /**
     * @covers Cradle\Module\Event\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['event_id']);
    }

    /**
     * @covers Cradle\Module\Event\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['event_id']);
    }

    /**
     * @covers Cradle\Module\Event\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'event_id' => $id,
        ]);

        $this->assertEquals($id, $actual['event_id']);
    }

    /**
     * @covers Cradle\Module\Event\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['event_id']);
    }

    /**
     * @covers Cradle\Module\Event\Service\SqlService::linkDeal
     */
    public function testLinkDeal()
    {
        $actual = $this->object->linkDeal(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['event_id']);
        $this->assertEquals(999, $actual['deal_id']);
    }

    /**
     * @covers Cradle\Module\Event\Service\SqlService::unlinkDeal
     */
    public function testUnlinkDeal()
    {
        $actual = $this->object->unlinkDeal(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['event_id']);
        $this->assertEquals(999, $actual['deal_id']);
    }
    

    /**
     * @covers Cradle\Module\Event\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['event_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Event\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['event_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Event\Service\SqlService::unlinkProfile
     */
    public function testUnlinkAllProfile()
    {
        $actual = $this->object->unlinkAllProfile(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['event_id']);
    }
    
}
