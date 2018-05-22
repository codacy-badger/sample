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
 * @package  Log
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Log_EventsTest extends PHPUnit_Framework_TestCase
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
     * log-create
     *
     * @covers Cradle\Module\Crawler\Log\Validator::getCreateErrors
     * @covers Cradle\Module\Crawler\Log\Validator::getOptionalErrors
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testLogCreate()
    {
        $this->request->setStage([
            'log_message' => 'Foobar Title',
            'log_link' => 'http://google.com/',
        ]);

        cradle()->trigger('log-create', $this->request, $this->response);
        $this->assertEquals('Foobar Title', $this->response->getResults('log_message'));
        $this->assertEquals('http://google.com/', $this->response->getResults('log_link'));
        self::$id = $this->response->getResults('log_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * log-detail
     *
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testLogDetail()
    {
        $this->request->setStage('log_id', 1);

        cradle()->trigger('log-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('log_id'));
    }

    /**
     * log-remove
     *
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testLogRemove()
    {
        $this->request->setStage('log_id', self::$id);

        cradle()->trigger('log-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('log_id'));
    }

    /**
     * log-search
     *
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::search
     * @covers Cradle\Module\Crawler\Log\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testLogSearch()
    {
        cradle()->trigger('log-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'log_id'));
    }

    /**
     * log-update
     *
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Crawler\Log\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testLogUpdate()
    {
        $this->request->setStage([
            'log_id' => self::$id,
            'log_message' => 'Foobar Title',
            'log_link' => 'http://google.com/',
        ]);

        cradle()->trigger('log-update', $this->request, $this->response);
        $this->assertEquals('Foobar Title', $this->response->getResults('log_message'));
        $this->assertEquals('http://google.com/', $this->response->getResults('log_link'));
        $this->assertEquals(self::$id, $this->response->getResults('log_id'));
    }
}
