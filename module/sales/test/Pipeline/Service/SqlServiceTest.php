<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Sales\Pipeline\Service;

/**
 * SQL service test
 * Pipeline Model Test
 *
 * @vendor   Acme
 * @package  Pipeline
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Sales_Pipeline_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['pipeline_id']);
    }

    /**
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['pipeline_id']);
    }

    /**
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['pipeline_id']);
    }

    /**
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'pipeline_id' => $id,
        ]);

        $this->assertEquals($id, $actual['pipeline_id']);
    }

    /**
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['pipeline_id']);
    }
}
