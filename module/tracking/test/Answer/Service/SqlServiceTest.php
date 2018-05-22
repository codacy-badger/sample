<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Answer\Service;

/**
 * SQL service test
 * Answer Model Test
 *
 * @vendor   Acme
 * @package  Answer
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Answer_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'answer_name' => 'John Doe',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['answer_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['answer_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();
        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['answer_id']);
        $this->assertEquals(1, $actual['rows'][0]['answer_active']);
        $this->assertEquals('John Doe', $actual['rows'][0]['answer_name']);
    }

    /**
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'answer_id' => $id,
        ]);

        $this->assertEquals($id, $actual['answer_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['answer_id']);
    }
}
