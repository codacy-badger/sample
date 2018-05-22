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
 * @package  Service
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Service_EventsTest extends PHPUnit_Framework_TestCase
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
     * service-create
     *
     * @covers Cradle\Module\Service\Validator::getCreateErrors
     * @covers Cradle\Module\Service\Validator::getOptionalErrors
     * @covers Cradle\Module\Service\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testServiceCreate()
    {
        $this->request->setStage([
            'service_name' => 'A Service',
            'service_credits' => ,
            'profile_id' => 1,
        ]);

        cradle()->trigger('service-create', $this->request, $this->response);
        $this->assertEquals('A Service', $this->response->getResults('service_name'));
        $this->assertEquals(, $this->response->getResults('service_credits'));
        self::$id = $this->response->getResults('service_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * service-detail
     *
     * @covers Cradle\Module\Service\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testServiceDetail()
    {
        $this->request->setStage('service_id', 1);

        cradle()->trigger('service-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('service_id'));
    }

    /**
     * service-remove
     *
     * @covers Cradle\Module\Service\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Service\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testServiceRemove()
    {
        $this->request->setStage('service_id', self::$id);

        cradle()->trigger('service-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('service_id'));
    }

    /**
     * service-restore
     *
     * @covers Cradle\Module\Service\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Service\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testServiceRestore()
    {
        $this->request->setStage('service_id', 581);

        cradle()->trigger('service-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('service_id'));
        $this->assertEquals(1, $this->response->getResults('service_active'));
    }

    /**
     * service-search
     *
     * @covers Cradle\Module\Service\Service\SqlService::search
     * @covers Cradle\Module\Service\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testServiceSearch()
    {
        cradle()->trigger('service-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'service_id'));
    }

    /**
     * service-update
     *
     * @covers Cradle\Module\Service\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Service\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testServiceUpdate()
    {
        $this->request->setStage([
            'service_id' => self::$id,
            'service_name' => 'A Service',
            'service_credits' => ,
            'profile_id' => 1,
        ]);

        cradle()->trigger('service-update', $this->request, $this->response);
        $this->assertEquals('A Service', $this->response->getResults('service_name'));
        $this->assertEquals(, $this->response->getResults('service_credits'));
        $this->assertEquals(self::$id, $this->response->getResults('service_id'));
    }
}
