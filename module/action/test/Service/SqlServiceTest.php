<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Action\Service;

/**
 * SQL service test
 * Action Model Test
 *
 * @vendor   Acme
 * @package  Action
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Action_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Action\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Action\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['action_id']);
    }

    /**
     * @covers Cradle\Module\Action\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['action_id']);
    }

    /**
     * @covers Cradle\Module\Action\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['action_id']);
    }

    /**
     * @covers Cradle\Module\Action\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'action_id' => $id,
        ]);

        $this->assertEquals($id, $actual['action_id']);
    }

    /**
     * @covers Cradle\Module\Action\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['action_id']);
    }

    /**
     * @covers Cradle\Module\Action\Service\SqlService::linkTemplate
     */
    public function testLinkTemplate()
    {
        $actual = $this->object->linkTemplate(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['action_id']);
        $this->assertEquals(999, $actual['template_id']);
    }

    /**
     * @covers Cradle\Module\Action\Service\SqlService::unlinkTemplate
     */
    public function testUnlinkTemplate()
    {
        $actual = $this->object->unlinkTemplate(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['action_id']);
        $this->assertEquals(999, $actual['template_id']);
    }
    
}
