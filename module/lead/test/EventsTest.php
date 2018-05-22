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
 * @package  Lead
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Lead_EventsTest extends PHPUnit_Framework_TestCase
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
     * lead-create
     *
     * @covers Cradle\Module\Lead\Validator::getCreateErrors
     * @covers Cradle\Module\Lead\Validator::getOptionalErrors
     * @covers Cradle\Module\Lead\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testLeadCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('lead-create', $this->request, $this->response);
        self::$id = $this->response->getResults('lead_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * lead-detail
     *
     * @covers Cradle\Module\Lead\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testLeadDetail()
    {
        $this->request->setStage('lead_id', 1);

        cradle()->trigger('lead-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('lead_id'));
    }

    /**
     * lead-remove
     *
     * @covers Cradle\Module\Lead\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Lead\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testLeadRemove()
    {
        $this->request->setStage('lead_id', self::$id);

        cradle()->trigger('lead-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('lead_id'));
    }

    /**
     * lead-restore
     *
     * @covers Cradle\Module\Lead\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Lead\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testLeadRestore()
    {
        $this->request->setStage('lead_id', 581);

        cradle()->trigger('lead-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('lead_id'));
        $this->assertEquals(1, $this->response->getResults('lead_active'));
    }

    /**
     * lead-search
     *
     * @covers Cradle\Module\Lead\Service\SqlService::search
     * @covers Cradle\Module\Lead\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testLeadSearch()
    {
        cradle()->trigger('lead-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'lead_id'));
    }

    /**
     * lead-update
     *
     * @covers Cradle\Module\Lead\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Lead\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testLeadUpdate()
    {
        $this->request->setStage([
            'lead_id' => self::$id,
        ]);

        cradle()->trigger('lead-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('lead_id'));
    }
}
