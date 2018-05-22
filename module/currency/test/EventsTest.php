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
 * @package  Currency
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Currency_EventsTest extends PHPUnit_Framework_TestCase
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
     * currency-create
     *
     * @covers Cradle\Module\Currency\Validator::getCreateErrors
     * @covers Cradle\Module\Currency\Validator::getOptionalErrors
     * @covers Cradle\Module\Currency\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testCurrencyCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('currency-create', $this->request, $this->response);
        self::$id = $this->response->getResults('currency_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * currency-detail
     *
     * @covers Cradle\Module\Currency\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testCurrencyDetail()
    {
        $this->request->setStage('currency_id', 1);

        cradle()->trigger('currency-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('currency_id'));
    }

    /**
     * currency-remove
     *
     * @covers Cradle\Module\Currency\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Currency\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testCurrencyRemove()
    {
        $this->request->setStage('currency_id', self::$id);

        cradle()->trigger('currency-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('currency_id'));
    }

    /**
     * currency-restore
     *
     * @covers Cradle\Module\Currency\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Currency\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testCurrencyRestore()
    {
        $this->request->setStage('currency_id', 581);

        cradle()->trigger('currency-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('currency_id'));
        $this->assertEquals(1, $this->response->getResults('currency_active'));
    }

    /**
     * currency-search
     *
     * @covers Cradle\Module\Currency\Service\SqlService::search
     * @covers Cradle\Module\Currency\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testCurrencySearch()
    {
        cradle()->trigger('currency-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'currency_id'));
    }

    /**
     * currency-update
     *
     * @covers Cradle\Module\Currency\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Currency\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testCurrencyUpdate()
    {
        $this->request->setStage([
            'currency_id' => self::$id,
        ]);

        cradle()->trigger('currency-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('currency_id'));
    }
}
