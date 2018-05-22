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
 * @package  Utm
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Utm_EventsTest extends PHPUnit_Framework_TestCase
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
     * utm-create
     *
     * @covers Cradle\Module\Utm\Validator::getCreateErrors
     * @covers Cradle\Module\Utm\Validator::getOptionalErrors
     * @covers Cradle\Module\Utm\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testUtmCreate()
    {
        $this->request->setStage([
            'utm_title' => 'Title',
            'utm_campaign' => 'UTM Campaign',
            'utm_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
        ]);

        cradle()->trigger('utm-create', $this->request, $this->response);
        $this->assertEquals('Title', $this->response->getResults('utm_title'));
        $this->assertEquals('UTM Campaign', $this->response->getResults('utm_campaign'));
        $this->assertEquals('One Two Three Four Five Six Seven Eight Nine Ten Eleven', $this->response->getResults('utm_detail'));
        self::$id = $this->response->getResults('utm_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * utm-detail
     *
     * @covers Cradle\Module\Utm\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testUtmDetail()
    {
        $this->request->setStage('utm_id', 1);

        cradle()->trigger('utm-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('utm_id'));
    }

    /**
     * utm-remove
     *
     * @covers Cradle\Module\Utm\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Utm\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testUtmRemove()
    {
        $this->request->setStage('utm_id', self::$id);

        cradle()->trigger('utm-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('utm_id'));
    }

    /**
     * utm-restore
     *
     * @covers Cradle\Module\Utm\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Utm\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testUtmRestore()
    {
        $this->request->setStage('utm_id', 581);

        cradle()->trigger('utm-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('utm_id'));
        $this->assertEquals(1, $this->response->getResults('utm_active'));
    }

    /**
     * utm-search
     *
     * @covers Cradle\Module\Utm\Service\SqlService::search
     * @covers Cradle\Module\Utm\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testUtmSearch()
    {
        cradle()->trigger('utm-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'utm_id'));
    }

    /**
     * utm-update
     *
     * @covers Cradle\Module\Utm\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Utm\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testUtmUpdate()
    {
        $this->request->setStage([
            'utm_id' => self::$id,
            'utm_title' => 'Title',
            'utm_campaign' => 'UTM Campaign',
            'utm_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
        ]);

        cradle()->trigger('utm-update', $this->request, $this->response);
        $this->assertEquals('Title', $this->response->getResults('utm_title'));
        $this->assertEquals('UTM Campaign', $this->response->getResults('utm_campaign'));
        $this->assertEquals('One Two Three Four Five Six Seven Eight Nine Ten Eleven', $this->response->getResults('utm_detail'));
        $this->assertEquals(self::$id, $this->response->getResults('utm_id'));
    }
}
