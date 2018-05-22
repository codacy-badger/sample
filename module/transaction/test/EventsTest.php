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
 * @package  Transaction
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Transaction_EventsTest extends PHPUnit_Framework_TestCase
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
     * transaction-create
     *
     * @covers Cradle\Module\Transaction\Validator::getCreateErrors
     * @covers Cradle\Module\Transaction\Validator::getOptionalErrors
     * @covers Cradle\Module\Transaction\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testTransactionCreate()
    {
        $this->request->setStage([
            'transaction_status' => 'pending',
            'transaction_payment_method' => 'paypal',
            'transaction_payment_reference' => '123456789',
            'transaction_profile' => ,
            'transaction_currency' => ,
            'transaction_total' => ,
            'transaction_credits' => ,
            'profile_id' => 1,
        ]);

        cradle()->trigger('transaction-create', $this->request, $this->response);
        $this->assertEquals('pending', $this->response->getResults('transaction_status'));
        $this->assertEquals('paypal', $this->response->getResults('transaction_payment_method'));
        $this->assertEquals('123456789', $this->response->getResults('transaction_payment_reference'));
        $this->assertEquals(, $this->response->getResults('transaction_profile'));
        $this->assertEquals(, $this->response->getResults('transaction_currency'));
        $this->assertEquals(, $this->response->getResults('transaction_total'));
        $this->assertEquals(, $this->response->getResults('transaction_credits'));
        self::$id = $this->response->getResults('transaction_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * transaction-detail
     *
     * @covers Cradle\Module\Transaction\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testTransactionDetail()
    {
        $this->request->setStage('transaction_id', 1);

        cradle()->trigger('transaction-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('transaction_id'));
    }

    /**
     * transaction-remove
     *
     * @covers Cradle\Module\Transaction\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Transaction\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testTransactionRemove()
    {
        $this->request->setStage('transaction_id', self::$id);

        cradle()->trigger('transaction-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('transaction_id'));
    }

    /**
     * transaction-restore
     *
     * @covers Cradle\Module\Transaction\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Transaction\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testTransactionRestore()
    {
        $this->request->setStage('transaction_id', 581);

        cradle()->trigger('transaction-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('transaction_id'));
        $this->assertEquals(1, $this->response->getResults('transaction_active'));
    }

    /**
     * transaction-search
     *
     * @covers Cradle\Module\Transaction\Service\SqlService::search
     * @covers Cradle\Module\Transaction\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testTransactionSearch()
    {
        cradle()->trigger('transaction-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'transaction_id'));
    }

    /**
     * transaction-update
     *
     * @covers Cradle\Module\Transaction\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Transaction\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testTransactionUpdate()
    {
        $this->request->setStage([
            'transaction_id' => self::$id,
            'transaction_status' => 'pending',
            'transaction_payment_method' => 'paypal',
            'transaction_payment_reference' => '123456789',
            'transaction_profile' => ,
            'transaction_currency' => ,
            'transaction_total' => ,
            'transaction_credits' => ,
            'profile_id' => 1,
        ]);

        cradle()->trigger('transaction-update', $this->request, $this->response);
        $this->assertEquals('pending', $this->response->getResults('transaction_status'));
        $this->assertEquals('paypal', $this->response->getResults('transaction_payment_method'));
        $this->assertEquals('123456789', $this->response->getResults('transaction_payment_reference'));
        $this->assertEquals(, $this->response->getResults('transaction_profile'));
        $this->assertEquals(, $this->response->getResults('transaction_currency'));
        $this->assertEquals(, $this->response->getResults('transaction_total'));
        $this->assertEquals(, $this->response->getResults('transaction_credits'));
        $this->assertEquals(self::$id, $this->response->getResults('transaction_id'));
    }
}
