<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Event test
 *
 * @vendor   Acme
 * @package  Blog
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Blog_EventsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var Request $response
     */
    protected $response;

    /**
     * @var int $id
     */
    protected static $id;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->request = new Request();
        $this->response = new Response();

        $this->request->load();
        $this->response->load();
    }

    /**
     * blog-create
     *
     * @covers Cradle\Module\Blog\Validator::getCreateErrors
     * @covers Cradle\Module\Blog\Validator::getOptionalErrors
     * @covers Cradle\Module\Blog\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testBlogCreate()
    {
        $this->request->setStage([
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
            'profile_id' => 1,
        ]);

        cradle()->trigger('blog-create', $this->request, $this->response);
        $this->assertEquals('A Title', $this->response->getResults('blog_title'));
        $this->assertEquals('A Slug', $this->response->getResults('blog_slug'));
        $this->assertEquals('A Article', $this->response->getResults('blog_article'));
        $this->assertEquals('A Description', $this->response->getResults('blog_description'));
        $this->assertEquals('A Facebook Title', $this->response->getResults('blog_facebook_title'));
        $this->assertEquals('A Facebook Image', $this->response->getResults('blog_facebook_image'));
        $this->assertEquals('A Facebook Description', $this->response->getResults('blog_facebook_description'));
        $this->assertEquals('A Twitter Title', $this->response->getResults('blog_twitter_title'));
        $this->assertEquals('A Twitter Image', $this->response->getResults('blog_twitter_image'));
        $this->assertEquals('A Twitter Description', $this->response->getResults('blog_twitter_description'));
        self::$id = $this->response->getResults('blog_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * blog-detail
     *
     * @covers Cradle\Module\Blog\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testBlogDetail()
    {
        $this->request->setStage('blog_id', 1);

        cradle()->trigger('blog-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('blog_id'));
    }

    /**
     * blog-remove
     *
     * @covers Cradle\Module\Blog\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Blog\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testBlogRemove()
    {
        $this->request->setStage('blog_id', self::$id);

        cradle()->trigger('blog-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('blog_id'));
    }

    /**
     * blog-restore
     *
     * @covers Cradle\Module\Blog\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Blog\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testBlogRestore()
    {
        $this->request->setStage('blog_id', 581);

        cradle()->trigger('blog-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('blog_id'));
        $this->assertEquals(1, $this->response->getResults('blog_active'));
    }

    /**
     * blog-search
     *
     * @covers Cradle\Module\Blog\Service\SqlService::search
     * @covers Cradle\Module\Blog\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testBlogSearch()
    {
        cradle()->trigger('blog-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'blog_id'));
    }

    /**
     * blog-update
     *
     * @covers Cradle\Module\Blog\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Blog\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testBlogUpdate()
    {
        $this->request->setStage([
            'blog_id' => self::$id,
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
            'profile_id' => 1,
        ]);

        cradle()->trigger('blog-update', $this->request, $this->response);
        $this->assertEquals('A Title', $this->response->getResults('blog_title'));
        $this->assertEquals('A Slug', $this->response->getResults('blog_slug'));
        $this->assertEquals('A Article', $this->response->getResults('blog_article'));
        $this->assertEquals('A Description', $this->response->getResults('blog_description'));
        $this->assertEquals('A Facebook Title', $this->response->getResults('blog_facebook_title'));
        $this->assertEquals('A Facebook Image', $this->response->getResults('blog_facebook_image'));
        $this->assertEquals('A Facebook Description', $this->response->getResults('blog_facebook_description'));
        $this->assertEquals('A Twitter Title', $this->response->getResults('blog_twitter_title'));
        $this->assertEquals('A Twitter Image', $this->response->getResults('blog_twitter_image'));
        $this->assertEquals('A Twitter Description', $this->response->getResults('blog_twitter_description'));
        $this->assertEquals(self::$id, $this->response->getResults('blog_id'));
    }
}
