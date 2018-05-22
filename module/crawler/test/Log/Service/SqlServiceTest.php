<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Log\Service;

/**
 * SQL service test
 * Log Model Test
 *
 * @vendor   Acme
 * @package  Log
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Log_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'log_message' => 'Foobar Title',
            'log_link' => 'http://google.com/',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['log_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['log_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['log_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'log_id' => $id,
            'log_message' => 'Foobar Title',
            'log_link' => 'http://google.com/',
        ]);

        $this->assertEquals($id, $actual['log_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['log_id']);
    }
}
