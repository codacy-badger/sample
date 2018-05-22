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
 * @package  Event
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Event_EventsTest extends PHPUnit_Framework_TestCase
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
     * event-create
     *
     * @covers Cradle\Module\Event\Validator::getCreateErrors
     * @covers Cradle\Module\Event\Validator::getOptionalErrors
     * @covers Cradle\Module\Event\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testEventCreate()
    {
        $this->request->setStage([
            'deal_id' => 1,
        ]);

        cradle()->trigger('event-create', $this->request, $this->response);
        self::$id = $this->response->getResults('event_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * event-detail
     *
     * @covers Cradle\Module\Event\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testEventDetail()
    {
        $this->request->setStage('event_id', 1);

        cradle()->trigger('event-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('event_id'));
    }

    /**
     * event-remove
     *
     * @covers Cradle\Module\Event\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Event\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testEventRemove()
    {
        $this->request->setStage('event_id', self::$id);

        cradle()->trigger('event-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('event_id'));
    }

    /**
     * event-restore
     *
     * @covers Cradle\Module\Event\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Event\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testEventRestore()
    {
        $this->request->setStage('event_id', 581);

        cradle()->trigger('event-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('event_id'));
        $this->assertEquals(1, $this->response->getResults('event_active'));
    }

    /**
     * event-search
     *
     * @covers Cradle\Module\Event\Service\SqlService::search
     * @covers Cradle\Module\Event\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testEventSearch()
    {
        cradle()->trigger('event-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'event_id'));
    }

    /**
     * event-update
     *
     * @covers Cradle\Module\Event\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Event\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testEventUpdate()
    {
        $this->request->setStage([
            'event_id' => self::$id,
            'deal_id' => 1,
        ]);

        cradle()->trigger('event-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('event_id'));
    }
}
