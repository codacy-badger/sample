<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Webpage\Service;

/**
 * SQL service test
 * Webpage Model Test
 *
 * @vendor   Acme
 * @package  Webpage
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Webpage_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'webpage_root' => 'Foobar Title',
            'webpage_link' => 'http://google.com/',
            'webpage_type' => 'detail',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['webpage_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['webpage_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['webpage_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'webpage_id' => $id,
            'webpage_root' => 'Foobar Title',
            'webpage_link' => 'http://google.com/',
            'webpage_type' => 'detail',
        ]);

        $this->assertEquals($id, $actual['webpage_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['webpage_id']);
    }
}
