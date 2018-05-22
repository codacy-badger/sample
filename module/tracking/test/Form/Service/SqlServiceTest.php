<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Form\Service;

/**
 * SQL service test
 * Form Model Test
 *
 * @vendor   Acme
 * @package  Form
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Form_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'form_name' => 'Foo Bar'
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['form_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['form_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['form_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'form_id' => $id,
        ]);

        $this->assertEquals($id, $actual['form_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['form_id']);
    }

    /**
     * @covers Cradle\Module\Form\Service\SqlService::linkQuestion
     */
    public function testLinkQuestion()
    {
        $actual = $this->object->linkQuestion(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['form_id']);
        $this->assertEquals(999, $actual['question_id']);

        $actual = $this->object->linkQuestion(1, 1);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(1, $actual['form_id']);
        $this->assertEquals(1, $actual['question_id']);
    }

    /**
     * @covers Cradle\Module\Form\Service\SqlService::unlinkQuestion
     */
    public function testUnlinkQuestion()
    {
        $actual = $this->object->unlinkQuestion(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['form_id']);
        $this->assertEquals(999, $actual['question_id']);
    }

    /**
     * @covers Cradle\Module\Form\Service\SqlService::unlinkQuestion
     */
    public function testUnlinkAllQuestion()
    {
        $actual = $this->object->unlinkAllQuestion(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['form_id']);
    }
    
}
