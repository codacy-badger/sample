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
 * @package  Accomplishment
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracer_Accomplishment_EventsTest extends PHPUnit_Framework_TestCase
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
     * accomplishment-create
     *
     * @covers Cradle\Module\Tracer\Accomplishment\Validator::getCreateErrors
     * @covers Cradle\Module\Tracer\Accomplishment\Validator::getOptionalErrors
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testAccomplishmentCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('accomplishment-create', $this->request, $this->response);
        self::$id = $this->response->getResults('accomplishment_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * accomplishment-detail
     *
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testAccomplishmentDetail()
    {
        $this->request->setStage('accomplishment_id', 1);

        cradle()->trigger('accomplishment-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('accomplishment_id'));
    }

    /**
     * accomplishment-remove
     *
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAccomplishmentRemove()
    {
        $this->request->setStage('accomplishment_id', self::$id);

        cradle()->trigger('accomplishment-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('accomplishment_id'));
    }

    /**
     * accomplishment-restore
     *
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAccomplishmentRestore()
    {
        $this->request->setStage('accomplishment_id', 581);

        cradle()->trigger('accomplishment-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('accomplishment_id'));
        $this->assertEquals(1, $this->response->getResults('accomplishment_active'));
    }

    /**
     * accomplishment-search
     *
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::search
     * @covers Cradle\Module\Tracer\Accomplishment\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testAccomplishmentSearch()
    {
        cradle()->trigger('accomplishment-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'accomplishment_id'));
    }

    /**
     * accomplishment-update
     *
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Accomplishment\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAccomplishmentUpdate()
    {
        $this->request->setStage([
            'accomplishment_id' => self::$id,
        ]);

        cradle()->trigger('accomplishment-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('accomplishment_id'));
    }
}
