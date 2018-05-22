<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Resume\Service;

/**
 * SQL service test
 * Resume Model Test
 *
 * @vendor   Acme
 * @package  Resume
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Resume_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Resume\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Resume\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'resume_position' => 'Programmer',
            'resume_link' => 'http://google.com',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['resume_id']);
    }

    /**
     * @covers Cradle\Module\Resume\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['resume_id']);
    }

    /**
     * @covers Cradle\Module\Resume\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['resume_id']);
    }

    /**
     * @covers Cradle\Module\Resume\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'resume_id' => $id,
            'resume_position' => 'Programmer',
            'resume_link' => 'http://google.com',
        ]);

        $this->assertEquals($id, $actual['resume_id']);
    }

    /**
     * @covers Cradle\Module\Resume\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['resume_id']);
    }
}
