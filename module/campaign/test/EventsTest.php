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
 * @package  Campaign
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Campaign_EventsTest extends PHPUnit_Framework_TestCase
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
     * campaign-create
     *
     * @covers Cradle\Module\Campaign\Validator::getCreateErrors
     * @covers Cradle\Module\Campaign\Validator::getOptionalErrors
     * @covers Cradle\Module\Campaign\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testCampaignCreate()
    {
        $this->request->setStage([
            'template_id' => 1,
        ]);

        cradle()->trigger('campaign-create', $this->request, $this->response);
        self::$id = $this->response->getResults('campaign_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * campaign-detail
     *
     * @covers Cradle\Module\Campaign\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testCampaignDetail()
    {
        $this->request->setStage('campaign_id', 1);

        cradle()->trigger('campaign-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('campaign_id'));
    }

    /**
     * campaign-remove
     *
     * @covers Cradle\Module\Campaign\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Campaign\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testCampaignRemove()
    {
        $this->request->setStage('campaign_id', self::$id);

        cradle()->trigger('campaign-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('campaign_id'));
    }

    /**
     * campaign-restore
     *
     * @covers Cradle\Module\Campaign\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Campaign\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testCampaignRestore()
    {
        $this->request->setStage('campaign_id', 581);

        cradle()->trigger('campaign-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('campaign_id'));
        $this->assertEquals(1, $this->response->getResults('campaign_active'));
    }

    /**
     * campaign-search
     *
     * @covers Cradle\Module\Campaign\Service\SqlService::search
     * @covers Cradle\Module\Campaign\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testCampaignSearch()
    {
        cradle()->trigger('campaign-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'campaign_id'));
    }

    /**
     * campaign-update
     *
     * @covers Cradle\Module\Campaign\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Campaign\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testCampaignUpdate()
    {
        $this->request->setStage([
            'campaign_id' => self::$id,
            'template_id' => 1,
        ]);

        cradle()->trigger('campaign-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('campaign_id'));
    }
}
