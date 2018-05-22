<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\File\Service;

/**
 * SQL service test
 * File Model Test
 *
 * @vendor   Acme
 * @package  File
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_File_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\File\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\File\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['file_id']);
    }

    /**
     * @covers Cradle\Module\File\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['file_id']);
    }

    /**
     * @covers Cradle\Module\File\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['file_id']);
    }

    /**
     * @covers Cradle\Module\File\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'file_id' => $id,
        ]);

        $this->assertEquals($id, $actual['file_id']);
    }

    /**
     * @covers Cradle\Module\File\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['file_id']);
    }

    /**
     * @covers Cradle\Module\File\Service\SqlService::linkComment
     */
    public function testLinkComment()
    {
        $actual = $this->object->linkComment(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['file_id']);
        $this->assertEquals(999, $actual['comment_id']);
    }

    /**
     * @covers Cradle\Module\File\Service\SqlService::unlinkComment
     */
    public function testUnlinkComment()
    {
        $actual = $this->object->unlinkComment(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['file_id']);
        $this->assertEquals(999, $actual['comment_id']);
    }
    
}
