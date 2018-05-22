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
 * @package  Label
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Label_EventsTest extends PHPUnit_Framework_TestCase
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
     * label-create
     *
     * @covers Cradle\Module\Tracking\Label\Validator::getCreateErrors
     * @covers Cradle\Module\Tracking\Label\Validator::getOptionalErrors
     * @covers Cradle\Module\Tracking\Label\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testLabelCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('label-create', $this->request, $this->response);
        self::$id = $this->response->getResults('label_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * label-detail
     *
     * @covers Cradle\Module\Tracking\Label\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testLabelDetail()
    {
        $this->request->setStage('label_id', 1);

        cradle()->trigger('label-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('label_id'));
    }

    /**
     * label-remove
     *
     * @covers Cradle\Module\Tracking\Label\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Label\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testLabelRemove()
    {
        $this->request->setStage('label_id', self::$id);

        cradle()->trigger('label-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('label_id'));
    }

    /**
     * label-restore
     *
     * @covers Cradle\Module\Tracking\Label\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Label\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testLabelRestore()
    {
        $this->request->setStage('label_id', 581);

        cradle()->trigger('label-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('label_id'));
        $this->assertEquals(1, $this->response->getResults('label_active'));
    }

    /**
     * label-search
     *
     * @covers Cradle\Module\Tracking\Label\Service\SqlService::search
     * @covers Cradle\Module\Tracking\Label\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testLabelSearch()
    {
        cradle()->trigger('label-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'label_id'));
    }

    /**
     * label-update
     *
     * @covers Cradle\Module\Tracking\Label\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Label\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testLabelUpdate()
    {
        $this->request->setStage([
            'label_id' => self::$id,
        ]);

        cradle()->trigger('label-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('label_id'));
    }
}
