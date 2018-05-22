<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Question\Service;

/**
 * SQL service test
 * Question Model Test
 *
 * @vendor   Acme
 * @package  Question
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Question_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'question_name' => 'What is your name',
            'question_choices' => '["John Doe","Foo Bar"]',
            'question_type' => 'choices',
            'answer_id' => 1
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['question_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1,$actual['question_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['question_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'question_id' => $id,
        ]);

        $this->assertEquals($id, $actual['question_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['question_id']);
    }

    /**
     * @covers Cradle\Module\Question\Service\SqlService::linkAnswer
     */
    public function testLinkAnswer()
    {
        $actual = $this->object->linkAnswer(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['question_id']);
        $this->assertEquals(999, $actual['answer_id']);
    }

    /**
     * @covers Cradle\Module\Question\Service\SqlService::unlinkAnswer
     */
    public function testUnlinkAnswer()
    {
        $actual = $this->object->unlinkAnswer(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['question_id']);
        $this->assertEquals(999, $actual['answer_id']);
    }

    /**
     * @covers Cradle\Module\Question\Service\SqlService::unlinkAnswer
     */
    public function testUnlinkAllAnswer()
    {
        $actual = $this->object->unlinkAllAnswer(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['question_id']);
    }
    
}
