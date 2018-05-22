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
 * @package  Question
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Question_EventsTest extends PHPUnit_Framework_TestCase
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
     * question-create
     *
     * @covers Cradle\Module\Tracking\Question\Validator::getCreateErrors
     * @covers Cradle\Module\Tracking\Question\Validator::getOptionalErrors
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testQuestionCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('question-create', $this->request, $this->response);
        self::$id = $this->response->getResults('question_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * question-detail
     *
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testQuestionDetail()
    {
        $this->request->setStage('question_id', 1);

        cradle()->trigger('question-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('question_id'));
    }

    /**
     * question-remove
     *
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testQuestionRemove()
    {
        $this->request->setStage('question_id', self::$id);

        cradle()->trigger('question-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('question_id'));
    }

    /**
     * question-restore
     *
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testQuestionRestore()
    {
        $this->request->setStage('question_id', 581);

        cradle()->trigger('question-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('question_id'));
        $this->assertEquals(1, $this->response->getResults('question_active'));
    }

    /**
     * question-search
     *
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::search
     * @covers Cradle\Module\Tracking\Question\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testQuestionSearch()
    {
        cradle()->trigger('question-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'question_id'));
    }

    /**
     * question-update
     *
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Question\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testQuestionUpdate()
    {
        $this->request->setStage([
            'question_id' => self::$id,
        ]);

        cradle()->trigger('question-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('question_id'));
    }
}
