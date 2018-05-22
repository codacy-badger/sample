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
 * @package  Area
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Area_EventsTest extends PHPUnit_Framework_TestCase
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
     * area-create
     *
     * @covers Cradle\Module\Area\Validator::getCreateErrors
     * @covers Cradle\Module\Area\Validator::getOptionalErrors
     * @covers Cradle\Module\Area\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testAreaCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('area-create', $this->request, $this->response);
        self::$id = $this->response->getResults('area_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * area-detail
     *
     * @covers Cradle\Module\Area\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testAreaDetail()
    {
        $this->request->setStage('area_id', 1);

        cradle()->trigger('area-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('area_id'));
    }

    /**
     * area-remove
     *
     * @covers Cradle\Module\Area\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Area\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAreaRemove()
    {
        $this->request->setStage('area_id', self::$id);

        cradle()->trigger('area-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('area_id'));
    }

    /**
     * area-restore
     *
     * @covers Cradle\Module\Area\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Area\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAreaRestore()
    {
        $this->request->setStage('area_id', 581);

        cradle()->trigger('area-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('area_id'));
        $this->assertEquals(1, $this->response->getResults('area_active'));
    }

    /**
     * area-search
     *
     * @covers Cradle\Module\Area\Service\SqlService::search
     * @covers Cradle\Module\Area\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testAreaSearch()
    {
        cradle()->trigger('area-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'area_id'));
    }

    /**
     * area-update
     *
     * @covers Cradle\Module\Area\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Area\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAreaUpdate()
    {
        $this->request->setStage([
            'area_id' => self::$id,
        ]);

        cradle()->trigger('area-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('area_id'));
    }
}
