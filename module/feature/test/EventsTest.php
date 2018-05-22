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
 * @package  Feature
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Feature_EventsTest extends PHPUnit_Framework_TestCase
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
     * feature-create
     *
     * @covers Cradle\Module\Feature\Validator::getCreateErrors
     * @covers Cradle\Module\Feature\Validator::getOptionalErrors
     * @covers Cradle\Module\Feature\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testFeatureCreate()
    {
        $this->request->setStage([
            'feature_name' => ,
            'feature_title' => ,
            'feature_slug' => ,
            'feature_detail' => ,
        ]);

        cradle()->trigger('feature-create', $this->request, $this->response);
        $this->assertEquals(, $this->response->getResults('feature_name'));
        $this->assertEquals(, $this->response->getResults('feature_title'));
        $this->assertEquals(, $this->response->getResults('feature_slug'));
        $this->assertEquals(, $this->response->getResults('feature_detail'));
        self::$id = $this->response->getResults('feature_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * feature-detail
     *
     * @covers Cradle\Module\Feature\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testFeatureDetail()
    {
        $this->request->setStage('feature_id', 1);

        cradle()->trigger('feature-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('feature_id'));
    }

    /**
     * feature-remove
     *
     * @covers Cradle\Module\Feature\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Feature\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testFeatureRemove()
    {
        $this->request->setStage('feature_id', self::$id);

        cradle()->trigger('feature-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('feature_id'));
    }

    /**
     * feature-restore
     *
     * @covers Cradle\Module\Feature\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Feature\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testFeatureRestore()
    {
        $this->request->setStage('feature_id', 581);

        cradle()->trigger('feature-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('feature_id'));
        $this->assertEquals(1, $this->response->getResults('feature_active'));
    }

    /**
     * feature-search
     *
     * @covers Cradle\Module\Feature\Service\SqlService::search
     * @covers Cradle\Module\Feature\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testFeatureSearch()
    {
        cradle()->trigger('feature-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'feature_id'));
    }

    /**
     * feature-update
     *
     * @covers Cradle\Module\Feature\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Feature\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testFeatureUpdate()
    {
        $this->request->setStage([
            'feature_id' => self::$id,
            'feature_name' => ,
            'feature_title' => ,
            'feature_slug' => ,
            'feature_detail' => ,
        ]);

        cradle()->trigger('feature-update', $this->request, $this->response);
        $this->assertEquals(, $this->response->getResults('feature_name'));
        $this->assertEquals(, $this->response->getResults('feature_title'));
        $this->assertEquals(, $this->response->getResults('feature_slug'));
        $this->assertEquals(, $this->response->getResults('feature_detail'));
        $this->assertEquals(self::$id, $this->response->getResults('feature_id'));
    }
}
