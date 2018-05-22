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
 * @package  Answer
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Answer_EventsTest extends PHPUnit_Framework_TestCase
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
     * answer-create
     *
     * @covers Cradle\Module\Tracking\Answer\Validator::getCreateErrors
     * @covers Cradle\Module\Tracking\Answer\Validator::getOptionalErrors
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testAnswerCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('answer-create', $this->request, $this->response);
        self::$id = $this->response->getResults('answer_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * answer-detail
     *
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testAnswerDetail()
    {
        $this->request->setStage('answer_id', 1);

        cradle()->trigger('answer-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('answer_id'));
    }

    /**
     * answer-remove
     *
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAnswerRemove()
    {
        $this->request->setStage('answer_id', self::$id);

        cradle()->trigger('answer-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('answer_id'));
    }

    /**
     * answer-restore
     *
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAnswerRestore()
    {
        $this->request->setStage('answer_id', 581);

        cradle()->trigger('answer-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('answer_id'));
        $this->assertEquals(1, $this->response->getResults('answer_active'));
    }

    /**
     * answer-search
     *
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::search
     * @covers Cradle\Module\Tracking\Answer\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testAnswerSearch()
    {
        cradle()->trigger('answer-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'answer_id'));
    }

    /**
     * answer-update
     *
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Answer\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAnswerUpdate()
    {
        $this->request->setStage([
            'answer_id' => self::$id,
        ]);

        cradle()->trigger('answer-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('answer_id'));
    }
}
