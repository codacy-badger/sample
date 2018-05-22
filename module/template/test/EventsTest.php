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
 * @package  Template
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Template_EventsTest extends PHPUnit_Framework_TestCase
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
     * template-create
     *
     * @covers Cradle\Module\Template\Validator::getCreateErrors
     * @covers Cradle\Module\Template\Validator::getOptionalErrors
     * @covers Cradle\Module\Template\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testTemplateCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('template-create', $this->request, $this->response);
        self::$id = $this->response->getResults('template_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * template-detail
     *
     * @covers Cradle\Module\Template\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testTemplateDetail()
    {
        $this->request->setStage('template_id', 1);

        cradle()->trigger('template-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('template_id'));
    }

    /**
     * template-remove
     *
     * @covers Cradle\Module\Template\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Template\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testTemplateRemove()
    {
        $this->request->setStage('template_id', self::$id);

        cradle()->trigger('template-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('template_id'));
    }

    /**
     * template-restore
     *
     * @covers Cradle\Module\Template\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Template\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testTemplateRestore()
    {
        $this->request->setStage('template_id', 581);

        cradle()->trigger('template-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('template_id'));
        $this->assertEquals(1, $this->response->getResults('template_active'));
    }

    /**
     * template-search
     *
     * @covers Cradle\Module\Template\Service\SqlService::search
     * @covers Cradle\Module\Template\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testTemplateSearch()
    {
        cradle()->trigger('template-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'template_id'));
    }

    /**
     * template-update
     *
     * @covers Cradle\Module\Template\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Template\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testTemplateUpdate()
    {
        $this->request->setStage([
            'template_id' => self::$id,
        ]);

        cradle()->trigger('template-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('template_id'));
    }
}
