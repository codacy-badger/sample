<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Sales\Deal\Service;

/**
 * SQL service test
 * Deal Model Test
 *
 * @vendor   Acme
 * @package  Deal
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Sales_Deal_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['deal_id']);
    }

    /**
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['deal_id']);
    }

    /**
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['deal_id']);
    }

    /**
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'deal_id' => $id,
        ]);

        $this->assertEquals($id, $actual['deal_id']);
    }

    /**
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['deal_id']);
    }

    /**
     * @covers Cradle\Module\Deal\Service\SqlService::linkCompany
     */
    public function testLinkCompany()
    {
        $actual = $this->object->linkCompany(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['deal_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Deal\Service\SqlService::unlinkCompany
     */
    public function testUnlinkCompany()
    {
        $actual = $this->object->unlinkCompany(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['deal_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }
    

    /**
     * @covers Cradle\Module\Deal\Service\SqlService::linkAgent
     */
    public function testLinkAgent()
    {
        $actual = $this->object->linkAgent(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['deal_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Deal\Service\SqlService::unlinkAgent
     */
    public function testUnlinkAgent()
    {
        $actual = $this->object->unlinkAgent(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['deal_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Deal\Service\SqlService::unlinkAgent
     */
    public function testUnlinkAllAgent()
    {
        $actual = $this->object->unlinkAllAgent(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['deal_id']);
    }
    

    /**
     * @covers Cradle\Module\Deal\Service\SqlService::linkPipeline
     */
    public function testLinkPipeline()
    {
        $actual = $this->object->linkPipeline(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['deal_id']);
        $this->assertEquals(999, $actual['pipeline_id']);
    }

    /**
     * @covers Cradle\Module\Deal\Service\SqlService::unlinkPipeline
     */
    public function testUnlinkPipeline()
    {
        $actual = $this->object->unlinkPipeline(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['deal_id']);
        $this->assertEquals(999, $actual['pipeline_id']);
    }
    
}
