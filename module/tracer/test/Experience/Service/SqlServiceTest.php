<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracer\Experience\Service;

/**
 * SQL service test
 * Experience Model Test
 *
 * @vendor   Acme
 * @package  Experience
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracer_Experience_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['experience_id']);
    }

    /**
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['experience_id']);
    }

    /**
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['experience_id']);
    }

    /**
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'experience_id' => $id,
        ]);

        $this->assertEquals($id, $actual['experience_id']);
    }

    /**
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['experience_id']);
    }

    /**
     * @covers Cradle\Module\Experience\Service\SqlService::linkInformation
     */
    public function testLinkInformation()
    {
        $actual = $this->object->linkInformation(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['experience_id']);
        $this->assertEquals(999, $actual['information_id']);
    }

    /**
     * @covers Cradle\Module\Experience\Service\SqlService::unlinkInformation
     */
    public function testUnlinkInformation()
    {
        $actual = $this->object->unlinkInformation(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['experience_id']);
        $this->assertEquals(999, $actual['information_id']);
    }

    /**
     * @covers Cradle\Module\Experience\Service\SqlService::unlinkInformation
     */
    public function testUnlinkAllInformation()
    {
        $actual = $this->object->unlinkAllInformation(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['experience_id']);
    }
    
}
