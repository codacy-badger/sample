<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Degree\Service;

/**
 * SQL service test
 * Degree Model Test
 *
 * @vendor   Acme
 * @package  Degree
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Degree_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Degree\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Degree\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['degree_id']);
    }

    /**
     * @covers Cradle\Module\Degree\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['degree_id']);
    }

    /**
     * @covers Cradle\Module\Degree\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['degree_id']);
    }

    /**
     * @covers Cradle\Module\Degree\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'degree_id' => $id,
        ]);

        $this->assertEquals($id, $actual['degree_id']);
    }

    /**
     * @covers Cradle\Module\Degree\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['degree_id']);
    }
}
