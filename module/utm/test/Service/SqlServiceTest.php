<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utm\Service;

/**
 * SQL service test
 * Utm Model Test
 *
 * @vendor   Acme
 * @package  Utm
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Utm_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Utm\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Utm\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'utm_title' => 'Title',
            'utm_campaign' => 'UTM Campaign',
            'utm_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'utm_source' => 'http://google.com',
            'utm_medium' => 'facebook'

        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['utm_id']);
    }

    /**
     * @covers Cradle\Module\Utm\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['utm_id']);
    }

    /**
     * @covers Cradle\Module\Utm\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['utm_id']);
    }

    /**
     * @covers Cradle\Module\Utm\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'utm_id' => $id,
            'utm_title' => 'Title',
            'utm_campaign' => 'UTM Campaign',
            'utm_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'utm_source' => 'http://google.com',
            'utm_medium' => 'facebook'
        ]);

        $this->assertEquals($id, $actual['utm_id']);
    }

    /**
     * @covers Cradle\Module\Utm\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['utm_id']);
    }
}
