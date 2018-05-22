<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ses\Service;

/**
 * SQL service test
 * Ses Model Test
 *
 * @vendor   Acme
 * @package  Ses
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ses_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Ses\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Ses\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['ses_id']);
    }

    /**
     * @covers Cradle\Module\Ses\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['ses_id']);
    }

    /**
     * @covers Cradle\Module\Ses\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['ses_id']);
    }

    /**
     * @covers Cradle\Module\Ses\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'ses_id' => $id,
        ]);

        $this->assertEquals($id, $actual['ses_id']);
    }

    /**
     * @covers Cradle\Module\Ses\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['ses_id']);
    }
}
