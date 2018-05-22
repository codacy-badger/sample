<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Website\Service;

/**
 * SQL service test
 * Website Model Test
 *
 * @vendor   Acme
 * @package  Website
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Website_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'website_name' => 'Acme Inc.',
            'website_root' => 'http://acme.com/',
            'website_start' => 'http://acme.com/start',
            'website_currency' => 'PHP',
            'website_locale' => 'philippines',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['website_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['website_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['website_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'website_id' => $id,
            'website_name' => 'Acme Inc.',
            'website_root' => 'http://acme.com/',
            'website_start' => 'http://acme.com/start',
            'website_currency' => 'PHP',
            'website_locale' => 'philippines',
        ]);

        $this->assertEquals($id, $actual['website_id']);
    }

    /**
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['website_id']);
    }
}
