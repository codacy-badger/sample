<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Term\Service;

/**
 * SQL service test
 * Term Model Test
 *
 * @vendor   Acme
 * @package  Term
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Term_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Term\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Term\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'term_name' => 'Apple',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['term_id']);
    }

    /**
     * @covers Cradle\Module\Term\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['term_id']);
    }

    /**
     * @covers Cradle\Module\Term\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['term_id']);
    }

    /**
     * @covers Cradle\Module\Term\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'term_id' => $id,
            'term_name' => 'Apple',
        ]);

        $this->assertEquals($id, $actual['term_id']);
    }

    /**
     * @covers Cradle\Module\Term\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['term_id']);
    }
}
