<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Interview\Interview_setting\Service;

/**
 * SQL service test
 * Interview_setting Model Test
 *
 * @vendor   Acme
 * @package  Interview_setting
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Interview_Interview_setting_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['interview_setting_id']);
    }

    /**
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(2);

        $this->assertEquals(2, $actual['interview_setting_id']);
    }

    /**
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(2, $actual['rows'][0]['interview_setting_id']);
    }

    /**
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'interview_setting_id' => $id,
        ]);

        $this->assertEquals($id, $actual['interview_setting_id']);
    }

    /**
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['interview_setting_id']);
    }

    /**
     * @covers Cradle\Module\InterviewSetting\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['interview_setting_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\InterviewSetting\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['interview_setting_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }
    
}