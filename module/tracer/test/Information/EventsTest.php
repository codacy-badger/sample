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
 * @package  Information
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracer_Information_EventsTest extends PHPUnit_Framework_TestCase
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
     * information-create
     *
     * @covers Cradle\Module\Tracer\Information\Validator::getCreateErrors
     * @covers Cradle\Module\Tracer\Information\Validator::getOptionalErrors
     * @covers Cradle\Module\Tracer\Information\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testInformationCreate()
    {
        $this->request->setStage([
            'profile_id' => 1,
        ]);

        cradle()->trigger('information-create', $this->request, $this->response);
        self::$id = $this->response->getResults('information_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * information-detail
     *
     * @covers Cradle\Module\Tracer\Information\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testInformationDetail()
    {
        $this->request->setStage('information_id', 1);

        cradle()->trigger('information-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('information_id'));
    }

    /**
     * information-remove
     *
     * @covers Cradle\Module\Tracer\Information\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Information\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testInformationRemove()
    {
        $this->request->setStage('information_id', self::$id);

        cradle()->trigger('information-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('information_id'));
    }

    /**
     * information-restore
     *
     * @covers Cradle\Module\Tracer\Information\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Information\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testInformationRestore()
    {
        $this->request->setStage('information_id', 581);

        cradle()->trigger('information-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('information_id'));
        $this->assertEquals(1, $this->response->getResults('information_active'));
    }

    /**
     * information-search
     *
     * @covers Cradle\Module\Tracer\Information\Service\SqlService::search
     * @covers Cradle\Module\Tracer\Information\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testInformationSearch()
    {
        cradle()->trigger('information-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'information_id'));
    }

    /**
     * information-update
     *
     * @covers Cradle\Module\Tracer\Information\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Information\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testInformationUpdate()
    {
        $this->request->setStage([
            'information_id' => self::$id,
            'profile_id' => 1,
        ]);

        cradle()->trigger('information-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('information_id'));
    }
}
