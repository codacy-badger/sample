<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Worker\Service;

/**
 * SQL service test
 * Worker Model Test
 *
 * @vendor   Acme
 * @package  Worker
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Worker_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'worker_root' => 'http://acme.com/',
            'worker_link' => 'http://google.com/',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['worker_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['worker_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['worker_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'worker_id' => $id,
            'worker_root' => 'http://acme.com/',
            'worker_link' => 'http://google.com/',
        ]);

        $this->assertEquals($id, $actual['worker_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['worker_id']);
    }
}
