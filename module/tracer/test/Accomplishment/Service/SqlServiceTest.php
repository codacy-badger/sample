<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracer\Accomplishment\Service;

/**
 * SQL service test
 * Accomplishment Model Test
 *
 * @vendor   Acme
 * @package  Accomplishment
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracer_Accomplishment_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['accomplishment_id']);
    }

    /**
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['accomplishment_id']);
    }

    /**
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['accomplishment_id']);
    }

    /**
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'accomplishment_id' => $id,
        ]);

        $this->assertEquals($id, $actual['accomplishment_id']);
    }

    /**
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['accomplishment_id']);
    }

    /**
     * @covers Cradle\Module\Accomplishment\Service\SqlService::linkInformation
     */
    public function testLinkInformation()
    {
        $actual = $this->object->linkInformation(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['accomplishment_id']);
        $this->assertEquals(999, $actual['information_id']);
    }

    /**
     * @covers Cradle\Module\Accomplishment\Service\SqlService::unlinkInformation
     */
    public function testUnlinkInformation()
    {
        $actual = $this->object->unlinkInformation(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['accomplishment_id']);
        $this->assertEquals(999, $actual['information_id']);
    }

    /**
     * @covers Cradle\Module\Accomplishment\Service\SqlService::unlinkInformation
     */
    public function testUnlinkAllInformation()
    {
        $actual = $this->object->unlinkAllInformation(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['accomplishment_id']);
    }
    
}
