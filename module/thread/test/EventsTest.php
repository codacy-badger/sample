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
 * @package  Thread
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Thread_EventsTest extends PHPUnit_Framework_TestCase
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
     * thread-create
     *
     * @covers Cradle\Module\Thread\Validator::getCreateErrors
     * @covers Cradle\Module\Thread\Validator::getOptionalErrors
     * @covers Cradle\Module\Thread\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testThreadCreate()
    {
        $this->request->setStage([
            'thread_gmail_id' => '263721638',
            'thread_subject' => 'Meeting',
            'deal_id' => 1,
        ]);

        cradle()->trigger('thread-create', $this->request, $this->response);
        $this->assertEquals('263721638', $this->response->getResults('thread_gmail_id'));
        $this->assertEquals('Meeting', $this->response->getResults('thread_subject'));
        self::$id = $this->response->getResults('thread_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * thread-detail
     *
     * @covers Cradle\Module\Thread\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testThreadDetail()
    {
        $this->request->setStage('thread_id', 1);

        cradle()->trigger('thread-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('thread_id'));
    }

    /**
     * thread-remove
     *
     * @covers Cradle\Module\Thread\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Thread\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testThreadRemove()
    {
        $this->request->setStage('thread_id', self::$id);

        cradle()->trigger('thread-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('thread_id'));
    }

    /**
     * thread-restore
     *
     * @covers Cradle\Module\Thread\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Thread\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testThreadRestore()
    {
        $this->request->setStage('thread_id', 581);

        cradle()->trigger('thread-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('thread_id'));
        $this->assertEquals(1, $this->response->getResults('thread_active'));
    }

    /**
     * thread-search
     *
     * @covers Cradle\Module\Thread\Service\SqlService::search
     * @covers Cradle\Module\Thread\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testThreadSearch()
    {
        cradle()->trigger('thread-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'thread_id'));
    }

    /**
     * thread-update
     *
     * @covers Cradle\Module\Thread\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Thread\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testThreadUpdate()
    {
        $this->request->setStage([
            'thread_id' => self::$id,
            'thread_gmail_id' => '263721638',
            'thread_subject' => 'Meeting',
            'history_id' => 1,
        ]);

        cradle()->trigger('thread-update', $this->request, $this->response);
        $this->assertEquals('263721638', $this->response->getResults('thread_gmail_id'));
        $this->assertEquals('Meeting', $this->response->getResults('thread_subject'));
        $this->assertEquals(self::$id, $this->response->getResults('thread_id'));
    }
}
