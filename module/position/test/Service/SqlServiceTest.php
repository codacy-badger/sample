<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Position\Service;

/**
 * SQL service test
 * Position Model Test
 *
 * @vendor   Acme
 * @package  Position
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Position_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Position\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Position\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'position_name' => 'John Doe',
            'position_description' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['position_id']);
    }

    /**
     * @covers Cradle\Module\Position\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['position_id']);
    }

    /**
     * @covers Cradle\Module\Position\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['position_id']);
    }

    /**
     * @covers Cradle\Module\Position\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'position_id' => $id,
            'position_name' => 'John Doe',
            'position_description' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
        ]);

        $this->assertEquals($id, $actual['position_id']);
    }

    /**
     * @covers Cradle\Module\Position\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['position_id']);
    }

}
