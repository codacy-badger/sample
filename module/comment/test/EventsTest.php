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
 * @package  Comment
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Comment_EventsTest extends PHPUnit_Framework_TestCase
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
     * comment-create
     *
     * @covers Cradle\Module\Comment\Validator::getCreateErrors
     * @covers Cradle\Module\Comment\Validator::getOptionalErrors
     * @covers Cradle\Module\Comment\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testCommentCreate()
    {
        $this->request->setStage([
            'comment_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'comment_type' => 'Foobar Title',
            'profile_id' => 1,
            'deal_id' => 1,
        ]);

        cradle()->trigger('comment-create', $this->request, $this->response);
        $this->assertEquals('One Two Three Four Five Six Seven Eight Nine Ten Eleven', $this->response->getResults('comment_detail'));
        $this->assertEquals('Foobar Title', $this->response->getResults('comment_type'));
        self::$id = $this->response->getResults('comment_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * comment-detail
     *
     * @covers Cradle\Module\Comment\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testCommentDetail()
    {
        $this->request->setStage('comment_id', 1);

        cradle()->trigger('comment-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('comment_id'));
    }

    /**
     * comment-remove
     *
     * @covers Cradle\Module\Comment\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Comment\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testCommentRemove()
    {
        $this->request->setStage('comment_id', self::$id);

        cradle()->trigger('comment-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('comment_id'));
    }

    /**
     * comment-restore
     *
     * @covers Cradle\Module\Comment\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Comment\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testCommentRestore()
    {
        $this->request->setStage('comment_id', 581);

        cradle()->trigger('comment-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('comment_id'));
        $this->assertEquals(1, $this->response->getResults('comment_active'));
    }

    /**
     * comment-search
     *
     * @covers Cradle\Module\Comment\Service\SqlService::search
     * @covers Cradle\Module\Comment\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testCommentSearch()
    {
        cradle()->trigger('comment-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'comment_id'));
    }

    /**
     * comment-update
     *
     * @covers Cradle\Module\Comment\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Comment\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testCommentUpdate()
    {
        $this->request->setStage([
            'comment_id' => self::$id,
            'comment_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'comment_type' => 'Foobar Title',
            'profile_id' => 1,
            'deal_id' => 1,
        ]);

        cradle()->trigger('comment-update', $this->request, $this->response);
        $this->assertEquals('One Two Three Four Five Six Seven Eight Nine Ten Eleven', $this->response->getResults('comment_detail'));
        $this->assertEquals('Foobar Title', $this->response->getResults('comment_type'));
        $this->assertEquals(self::$id, $this->response->getResults('comment_id'));
    }
}
