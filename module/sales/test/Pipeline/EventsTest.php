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
 * @package  Pipeline
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Sales_Pipeline_EventsTest extends PHPUnit_Framework_TestCase
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
     * pipeline-create
     *
     * @covers Cradle\Module\Sales\Pipeline\Validator::getCreateErrors
     * @covers Cradle\Module\Sales\Pipeline\Validator::getOptionalErrors
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testPipelineCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('pipeline-create', $this->request, $this->response);
        self::$id = $this->response->getResults('pipeline_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * pipeline-detail
     *
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testPipelineDetail()
    {
        $this->request->setStage('pipeline_id', 1);

        cradle()->trigger('pipeline-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('pipeline_id'));
    }

    /**
     * pipeline-remove
     *
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testPipelineRemove()
    {
        $this->request->setStage('pipeline_id', self::$id);

        cradle()->trigger('pipeline-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('pipeline_id'));
    }

    /**
     * pipeline-restore
     *
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testPipelineRestore()
    {
        $this->request->setStage('pipeline_id', 581);

        cradle()->trigger('pipeline-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('pipeline_id'));
        $this->assertEquals(1, $this->response->getResults('pipeline_active'));
    }

    /**
     * pipeline-search
     *
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::search
     * @covers Cradle\Module\Sales\Pipeline\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testPipelineSearch()
    {
        cradle()->trigger('pipeline-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'pipeline_id'));
    }

    /**
     * pipeline-update
     *
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Sales\Pipeline\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testPipelineUpdate()
    {
        $this->request->setStage([
            'pipeline_id' => self::$id,
        ]);

        cradle()->trigger('pipeline-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('pipeline_id'));
    }
}
