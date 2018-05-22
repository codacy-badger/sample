<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Interview\Interview_schedule\Service;

/**
 * SQL service test
 * Interview_schedule Model Test
 *
 * @vendor   Acme
 * @package  Interview_schedule
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Interview_Interview_schedule_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['interview_schedule_id']);
    }

    /**
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['interview_schedule_id']);
    }

    /**
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['interview_schedule_id']);
    }

    /**
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'interview_schedule_id' => $id,
        ]);

        $this->assertEquals($id, $actual['interview_schedule_id']);
    }

    /**
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['interview_schedule_id']);
    }

    /**
     * @covers Cradle\Module\InterviewSchedule\Service\SqlService::linkInterviewSetting
     */
    public function testLinkInterviewSetting()
    {
        $actual = $this->object->linkInterviewSetting(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['interview_schedule_id']);
        $this->assertEquals(999, $actual['interview_setting_id']);
    }

    /**
     * @covers Cradle\Module\InterviewSchedule\Service\SqlService::unlinkInterviewSetting
     */
    public function testUnlinkInterviewSetting()
    {
        $actual = $this->object->unlinkInterviewSetting(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['interview_schedule_id']);
        $this->assertEquals(999, $actual['interview_setting_id']);
    }
    

    /**
     * @covers Cradle\Module\InterviewSchedule\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['interview_schedule_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\InterviewSchedule\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['interview_schedule_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }
    

    /**
     * @covers Cradle\Module\InterviewSchedule\Service\SqlService::linkPost
     */
    public function testLinkPost()
    {
        $actual = $this->object->linkPost(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['interview_schedule_id']);
        $this->assertEquals(999, $actual['post_id']);
    }

    /**
     * @covers Cradle\Module\InterviewSchedule\Service\SqlService::unlinkPost
     */
    public function testUnlinkPost()
    {
        $actual = $this->object->unlinkPost(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['interview_schedule_id']);
        $this->assertEquals(999, $actual['post_id']);
    }
    
}
