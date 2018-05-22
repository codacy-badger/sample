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
 * @package  Form
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Form_EventsTest extends PHPUnit_Framework_TestCase
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
     * form-create
     *
     * @covers Cradle\Module\Tracking\Form\Validator::getCreateErrors
     * @covers Cradle\Module\Tracking\Form\Validator::getOptionalErrors
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testFormCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('form-create', $this->request, $this->response);
        self::$id = $this->response->getResults('form_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * form-detail
     *
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testFormDetail()
    {
        $this->request->setStage('form_id', 1);

        cradle()->trigger('form-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('form_id'));
    }

    /**
     * form-remove
     *
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testFormRemove()
    {
        $this->request->setStage('form_id', self::$id);

        cradle()->trigger('form-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('form_id'));
    }

    /**
     * form-restore
     *
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testFormRestore()
    {
        $this->request->setStage('form_id', 581);

        cradle()->trigger('form-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('form_id'));
        $this->assertEquals(1, $this->response->getResults('form_active'));
    }

    /**
     * form-search
     *
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::search
     * @covers Cradle\Module\Tracking\Form\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testFormSearch()
    {
        cradle()->trigger('form-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'form_id'));
    }

    /**
     * form-update
     *
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Form\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testFormUpdate()
    {
        $this->request->setStage([
            'form_id' => self::$id,
        ]);

        cradle()->trigger('form-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('form_id'));
    }
}
