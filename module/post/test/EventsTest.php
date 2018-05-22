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
 * @package  Post
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Post_EventsTest extends PHPUnit_Framework_TestCase
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
     * post-create
     *
     * @covers Cradle\Module\Post\Validator::getCreateErrors
     * @covers Cradle\Module\Post\Validator::getOptionalErrors
     * @covers Cradle\Module\Post\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testPostCreate()
    {
        $this->request->setStage([
            'post_name' => 'John Doe',
            'post_position' => 'Foobar Title',
            'post_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'profile_id' => 1,
        ]);

        cradle()->trigger('post-create', $this->request, $this->response);
        $this->assertEquals('John Doe', $this->response->getResults('post_name'));
        $this->assertEquals('Foobar Title', $this->response->getResults('post_position'));
        $this->assertEquals('One Two Three Four Five Six Seven Eight Nine Ten Eleven', $this->response->getResults('post_detail'));
        self::$id = $this->response->getResults('post_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * post-detail
     *
     * @covers Cradle\Module\Post\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testPostDetail()
    {
        $this->request->setStage('post_id', 1);

        cradle()->trigger('post-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('post_id'));
    }

    /**
     * post-remove
     *
     * @covers Cradle\Module\Post\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Post\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testPostRemove()
    {
        $this->request->setStage('post_id', self::$id);

        cradle()->trigger('post-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('post_id'));
    }

    /**
     * post-restore
     *
     * @covers Cradle\Module\Post\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Post\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testPostRestore()
    {
        $this->request->setStage('post_id', 581);

        cradle()->trigger('post-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('post_id'));
        $this->assertEquals(1, $this->response->getResults('post_active'));
    }

    /**
     * post-search
     *
     * @covers Cradle\Module\Post\Service\SqlService::search
     * @covers Cradle\Module\Post\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testPostSearch()
    {
        cradle()->trigger('post-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'post_id'));
    }

    /**
     * post-update
     *
     * @covers Cradle\Module\Post\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Post\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testPostUpdate()
    {
        $this->request->setStage([
            'post_id' => self::$id,
            'post_name' => 'John Doe',
            'post_position' => 'Foobar Title',
            'post_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'profile_id' => 1,
        ]);

        cradle()->trigger('post-update', $this->request, $this->response);
        $this->assertEquals('John Doe', $this->response->getResults('post_name'));
        $this->assertEquals('Foobar Title', $this->response->getResults('post_position'));
        $this->assertEquals('One Two Three Four Five Six Seven Eight Nine Ten Eleven', $this->response->getResults('post_detail'));
        $this->assertEquals(self::$id, $this->response->getResults('post_id'));
    }
}
