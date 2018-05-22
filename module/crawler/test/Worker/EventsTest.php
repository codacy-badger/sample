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
 * @package  Worker
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Worker_EventsTest extends PHPUnit_Framework_TestCase
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
     * worker-create
     *
     * @covers Cradle\Module\Crawler\Worker\Validator::getCreateErrors
     * @covers Cradle\Module\Crawler\Worker\Validator::getOptionalErrors
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testWorkerCreate()
    {
        $this->request->setStage([
            'worker_root' => 'http://acme.com/',
            'worker_link' => 'http://google.com/',
        ]);

        cradle()->trigger('worker-create', $this->request, $this->response);
        $this->assertEquals('http://acme.com/', $this->response->getResults('worker_root'));
        $this->assertEquals('http://google.com/', $this->response->getResults('worker_link'));
        self::$id = $this->response->getResults('worker_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * worker-detail
     *
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testWorkerDetail()
    {
        $this->request->setStage('worker_id', 1);

        cradle()->trigger('worker-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('worker_id'));
    }

    /**
     * worker-remove
     *
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testWorkerRemove()
    {
        $this->request->setStage('worker_id', self::$id);

        cradle()->trigger('worker-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('worker_id'));
    }

    /**
     * worker-search
     *
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::search
     * @covers Cradle\Module\Crawler\Worker\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testWorkerSearch()
    {
        cradle()->trigger('worker-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'worker_id'));
    }

    /**
     * worker-update
     *
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Crawler\Worker\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testWorkerUpdate()
    {
        $this->request->setStage([
            'worker_id' => self::$id,
            'worker_root' => 'http://acme.com/',
            'worker_link' => 'http://google.com/',
        ]);

        cradle()->trigger('worker-update', $this->request, $this->response);
        $this->assertEquals('http://acme.com/', $this->response->getResults('worker_root'));
        $this->assertEquals('http://google.com/', $this->response->getResults('worker_link'));
        $this->assertEquals(self::$id, $this->response->getResults('worker_id'));
    }
}
