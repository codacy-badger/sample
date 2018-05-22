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
 * @package  Action
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Action_EventsTest extends PHPUnit_Framework_TestCase
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
     * action-create
     *
     * @covers Cradle\Module\Action\Validator::getCreateErrors
     * @covers Cradle\Module\Action\Validator::getOptionalErrors
     * @covers Cradle\Module\Action\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testActionCreate()
    {
        $this->request->setStage([
            'template_id' => 1,
        ]);

        cradle()->trigger('action-create', $this->request, $this->response);
        self::$id = $this->response->getResults('action_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * action-detail
     *
     * @covers Cradle\Module\Action\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testActionDetail()
    {
        $this->request->setStage('action_id', 1);

        cradle()->trigger('action-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('action_id'));
    }

    /**
     * action-remove
     *
     * @covers Cradle\Module\Action\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Action\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testActionRemove()
    {
        $this->request->setStage('action_id', self::$id);

        cradle()->trigger('action-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('action_id'));
    }

    /**
     * action-restore
     *
     * @covers Cradle\Module\Action\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Action\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testActionRestore()
    {
        $this->request->setStage('action_id', 581);

        cradle()->trigger('action-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('action_id'));
        $this->assertEquals(1, $this->response->getResults('action_active'));
    }

    /**
     * action-search
     *
     * @covers Cradle\Module\Action\Service\SqlService::search
     * @covers Cradle\Module\Action\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testActionSearch()
    {
        cradle()->trigger('action-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'action_id'));
    }

    /**
     * action-update
     *
     * @covers Cradle\Module\Action\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Action\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testActionUpdate()
    {
        $this->request->setStage([
            'action_id' => self::$id,
            'template_id' => 1,
        ]);

        cradle()->trigger('action-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('action_id'));
    }
}
