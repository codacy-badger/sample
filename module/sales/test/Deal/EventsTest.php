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
 * @package  Deal
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Sales_Deal_EventsTest extends PHPUnit_Framework_TestCase
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
     * deal-create
     *
     * @covers Cradle\Module\Sales\Deal\Validator::getCreateErrors
     * @covers Cradle\Module\Sales\Deal\Validator::getOptionalErrors
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testDealCreate()
    {
        $this->request->setStage([
            'profile_id' => 1,
            'pipeline_id' => 1,
        ]);

        cradle()->trigger('deal-create', $this->request, $this->response);
        self::$id = $this->response->getResults('deal_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * deal-detail
     *
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testDealDetail()
    {
        $this->request->setStage('deal_id', 1);

        cradle()->trigger('deal-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('deal_id'));
    }

    /**
     * deal-remove
     *
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testDealRemove()
    {
        $this->request->setStage('deal_id', self::$id);

        cradle()->trigger('deal-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('deal_id'));
    }

    /**
     * deal-restore
     *
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testDealRestore()
    {
        $this->request->setStage('deal_id', 581);

        cradle()->trigger('deal-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('deal_id'));
        $this->assertEquals(1, $this->response->getResults('deal_active'));
    }

    /**
     * deal-search
     *
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::search
     * @covers Cradle\Module\Sales\Deal\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testDealSearch()
    {
        cradle()->trigger('deal-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'deal_id'));
    }

    /**
     * deal-update
     *
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Sales\Deal\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testDealUpdate()
    {
        $this->request->setStage([
            'deal_id' => self::$id,
            'profile_id' => 1,
            'pipeline_id' => 1,
        ]);

        cradle()->trigger('deal-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('deal_id'));
    }
}
