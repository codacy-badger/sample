<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Thread\Service;

/**
 * SQL service test
 * Thread Model Test
 *
 * @vendor   Acme
 * @package  Thread
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Thread_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Thread\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Thread\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'thread_gmail_id' => '263721638',
            'thread_subject' => 'Meeting',
            'deal_id' => 1
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['thread_id']);
    }

    /**
     * @covers Cradle\Module\Thread\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['thread_id']);
    }

    /**
     * @covers Cradle\Module\Thread\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['thread_id']);
    }

    /**
     * @covers Cradle\Module\Thread\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'thread_id' => $id,
            'thread_gmail_id' => '263721638',
            'thread_subject' => 'Meeting',
        ]);

        $this->assertEquals($id, $actual['thread_id']);
    }

    /**
     * @covers Cradle\Module\Thread\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['thread_id']);
    }
}
