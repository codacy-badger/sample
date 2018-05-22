<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Profile\Service;

/**
 * SQL service test
 * Profile Model Test
 *
 * @vendor   Acme
 * @package  Profile
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Profile_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Profile\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Profile\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'profile_name' => 'John Doe',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['profile_id']);

        // for accceptance test use only
        $actual = $this->object->create([
            'profile_name' => 'Test User',
            'profile_email' => 'test@gmail.com',
            'profile_phone' => '09999999999',
            'profile_slug' => 'Test-User-u5',
            // 'profile_company' => 'Openovate Labs',
            'profile_credits' => '50000'
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['profile_id']);

        $actual = $this->object->create([
            'profile_name' => 'Test Seeker User',
            'profile_email' => 'testseeker@gmail.com',
            'profile_slug' => 'Test-Seeker-User-u5'
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Profile\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Profile\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['profile_id']);
    }

    /**
     * @covers Cradle\Module\Profile\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'profile_id' => 1,
            'profile_name' => 'John Doe',
            'profile_package' => '["unlimited-post", "unlimited-resume", "sms-match", "ats", "interview-scheduler","tracer-study"]'
        ]);

        $this->assertEquals(1, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Profile\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['profile_id']);
    }
}
