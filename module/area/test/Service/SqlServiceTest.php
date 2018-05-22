<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Area\Service;

/**
 * SQL service test
 * Area Model Test
 *
 * @vendor   Acme
 * @package  Area
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Area_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Area\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Area\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'area_name' => 'Metro Manila'
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['area_id']);
    }

    /**
     * @covers Cradle\Module\Area\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['area_id']);
    }

    /**
     * @covers Cradle\Module\Area\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['area_id']);
    }

    /**
     * @covers Cradle\Module\Area\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'area_id' => $id,
        ]);

        $this->assertEquals($id, $actual['area_id']);
    }

    /**
     * @covers Cradle\Module\Area\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['area_id']);
    }
}
