<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Blog\Service;

/**
 * SQL service test
 * Blog Model Test
 *
 * @vendor   Acme
 * @package  Blog
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Blog_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Blog\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Blog\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'blog_title' => 'A Title',
            'blog_slug' => 'A Slug',
            'blog_article' => 'A Article',
            'blog_description' => 'A Description',
            'blog_facebook_title' => 'A Facebook Title',
            'blog_facebook_image' => 'A Facebook Image',
            'blog_facebook_description' => 'A Facebook Description',
            'blog_twitter_title' => 'A Twitter Title',
            'blog_twitter_image' => 'A Twitter Image',
            'blog_twitter_description' => 'A Twitter Description',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['blog_id']);
    }

    /**
     * @covers Cradle\Module\Blog\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['blog_id']);
    }

    /**
     * @covers Cradle\Module\Blog\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['blog_id']);
    }

    /**
     * @covers Cradle\Module\Blog\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'blog_id' => $id,
            'blog_title' => 'A Title',
            'blog_slug' => 'A Slug',
            'blog_article' => 'A Article',
            'blog_description' => 'A Description',
            'blog_facebook_title' => 'A Facebook Title',
            'blog_facebook_image' => 'A Facebook Image',
            'blog_facebook_description' => 'A Facebook Description',
            'blog_twitter_title' => 'A Twitter Title',
            'blog_twitter_image' => 'A Twitter Image',
            'blog_twitter_description' => 'A Twitter Description',
        ]);

        $this->assertEquals($id, $actual['blog_id']);
    }

    /**
     * @covers Cradle\Module\Blog\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['blog_id']);
    }

    /**
     * @covers Cradle\Module\Blog\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['blog_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Blog\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['blog_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }
    
}
