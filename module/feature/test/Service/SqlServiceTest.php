<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Feature\Service;

/**
 * SQL service test
 * Feature Model Test
 *
 * @vendor   Acme
 * @package  Feature
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Feature_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Feature\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Feature\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'feature_name' => '',
            'feature_title' => '',
            'feature_slug' => '',
            'feature_detail' => ''
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['feature_id']);
    }

    /**
     * @covers Cradle\Module\Feature\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['feature_id']);
    }

    /**
     * @covers Cradle\Module\Feature\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['feature_id']);
    }

    /**
     * @covers Cradle\Module\Feature\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'feature_id' => $id,
            'feature_name' => '',
            'feature_title' => '',
            'feature_slug' => '',
            'feature_detail' => ''
        ]);

        $this->assertEquals($id, $actual['feature_id']);
    }

    /**
     * @covers Cradle\Module\Feature\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['feature_id']);
    }
}
