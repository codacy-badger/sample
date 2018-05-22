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
 * @package  Degree
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Degree_EventsTest extends PHPUnit_Framework_TestCase
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
     * degree-create
     *
     * @covers Cradle\Module\Degree\Validator::getCreateErrors
     * @covers Cradle\Module\Degree\Validator::getOptionalErrors
     * @covers Cradle\Module\Degree\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testDegreeCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('degree-create', $this->request, $this->response);
        self::$id = $this->response->getResults('degree_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * degree-detail
     *
     * @covers Cradle\Module\Degree\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testDegreeDetail()
    {
        $this->request->setStage('degree_id', 1);

        cradle()->trigger('degree-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('degree_id'));
    }

    /**
     * degree-remove
     *
     * @covers Cradle\Module\Degree\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Degree\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testDegreeRemove()
    {
        $this->request->setStage('degree_id', self::$id);

        cradle()->trigger('degree-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('degree_id'));
    }

    /**
     * degree-restore
     *
     * @covers Cradle\Module\Degree\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Degree\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testDegreeRestore()
    {
        $this->request->setStage('degree_id', 581);

        cradle()->trigger('degree-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('degree_id'));
        $this->assertEquals(1, $this->response->getResults('degree_active'));
    }

    /**
     * degree-search
     *
     * @covers Cradle\Module\Degree\Service\SqlService::search
     * @covers Cradle\Module\Degree\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testDegreeSearch()
    {
        cradle()->trigger('degree-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'degree_id'));
    }

    /**
     * degree-update
     *
     * @covers Cradle\Module\Degree\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Degree\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testDegreeUpdate()
    {
        $this->request->setStage([
            'degree_id' => self::$id,
        ]);

        cradle()->trigger('degree-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('degree_id'));
    }
}
