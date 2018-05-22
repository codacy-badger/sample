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
 * Elastic service test
 *
 * @vendor   Acme
 * @package  Answer
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Answer_Service_ElasticServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ElasticService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Tracking\Answer\Service\ElasticService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('elastic');
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::remove
     */
    public function testRemove()
    {
        $actual = $this->object->remove(1);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertEquals('answer', $actual['_index']);
        $this->assertEquals('main', $actual['_type']);
        $this->assertEquals(1, $actual['_id']);
        $this->assertEquals('deleted', $actual['result']);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create(1);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertEquals('answer', $actual['_index']);
        $this->assertEquals('main', $actual['_type']);
        $this->assertEquals(1, $actual['_id']);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertEquals(1, $actual['answer_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Answer\Service\ElasticService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['answer_id']);
    }

    /**
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     */
    public function testUpdate()
    {
        $this->object->create(1);

        $actual = $this->object->update(1);

        //if it's false, it's not enabled
        if($actual === false) {
            return;
        }

        // now, test it
        $this->assertEquals('answer', $actual['_index']);
        $this->assertEquals('main', $actual['_type']);
        $this->assertEquals(1, $actual['_id']);
        $this->assertEquals('noop', $actual['result']);
    }
}
