<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Post\Service;

/**
 * SQL service test
 * Post Model Test
 *
 * @vendor   Acme
 * @package  Post
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Post_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Post\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Post\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'post_name' => 'John Doe',
            'post_position' => 'Foobar Little Test',
            'post_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['post_id']);
    }

    /**
     * @covers Cradle\Module\Post\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(['1', 'Google Ventures']);

        $this->assertEquals(1, $actual['post_id']);
        $this->assertEquals('Google Ventures', $actual['post_name']);
    }

    /**
     * @covers Cradle\Module\Post\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search(['1', 'Google Ventures', 'john@doe.com', 'Senior Backend Developer']);

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['post_id']);
        $this->assertEquals('Google Ventures', $actual['rows'][0]['post_name']);
        $this->assertEquals('john@doe.com', $actual['rows'][0]['post_email']);
        $this->assertEquals('09999999999', $actual['rows'][0]['post_phone']);
        $this->assertEquals('Senior Backend Developer', $actual['rows'][0]['post_position']);
    }

    /**
     * @covers Cradle\Module\Post\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'post_id' => 6,
            'post_name' => 'John Ventures',
            'post_position' => 'Foobar Big Test',
            'post_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'post_email' => 'cy@doe.com',
            'post_phone' => '112-5432',
            'post_location' => 'Caloocan City'
        ]);

        $this->assertEquals(6, $actual['post_id']);
    }

    /**
     * @covers Cradle\Module\Post\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['post_id']);
    }

    /**
     * @covers Cradle\Module\Post\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(6, 1);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(6, $actual['post_id']);
        $this->assertEquals(1, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Post\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(6, 1);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(6, $actual['post_id']);
        $this->assertEquals(1, $actual['profile_id']);
    }
    

    /**
     * @covers Cradle\Module\Post\Service\SqlService::linkComment
     */
    public function testLinkComment()
    {
        $actual = $this->object->linkComment(6, 1);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(6, $actual['post_id']);
        $this->assertEquals(1, $actual['comment_id']);
    }

    /**
     * @covers Cradle\Module\Post\Service\SqlService::unlinkComment
     */
    public function testUnlinkComment()
    {
        $actual = $this->object->unlinkComment(6, 1);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(6, $actual['post_id']);
        $this->assertEquals(1, $actual['comment_id']);
    }

    /**
     * @covers Cradle\Module\Post\Service\SqlService::unlinkComment
     */
    public function testUnlinkAllComment()
    {
        $actual = $this->object->unlinkAllComment(6);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(6, $actual['post_id']);
    }
}
