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
 * @package  Interview_setting
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Interview_Interview_setting_EventsTest extends PHPUnit_Framework_TestCase
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
     * interview_setting-create
     *
     * @covers Cradle\Module\Interview\Interview_setting\Validator::getCreateErrors
     * @covers Cradle\Module\Interview\Interview_setting\Validator::getOptionalErrors
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testInterviewSettingCreate()
    {
        $this->request->setStage([
            'profile_id' => 1,
        ]);

        cradle()->trigger('interview_setting-create', $this->request, $this->response);
        self::$id = $this->response->getResults('interview_setting_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * interview_setting-detail
     *
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testInterviewSettingDetail()
    {
        $this->request->setStage('interview_setting_id', 1);

        cradle()->trigger('interview_setting-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('interview_setting_id'));
    }

    /**
     * interview_setting-remove
     *
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testInterviewSettingRemove()
    {
        $this->request->setStage('interview_setting_id', self::$id);

        cradle()->trigger('interview_setting-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('interview_setting_id'));
    }

    /**
     * interview_setting-restore
     *
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testInterviewSettingRestore()
    {
        $this->request->setStage('interview_setting_id', 581);

        cradle()->trigger('interview_setting-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('interview_setting_id'));
        $this->assertEquals(1, $this->response->getResults('interview_setting_active'));
    }

    /**
     * interview_setting-search
     *
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::search
     * @covers Cradle\Module\Interview\Interview_setting\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testInterviewSettingSearch()
    {
        cradle()->trigger('interview_setting-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'interview_setting_id'));
    }

    /**
     * interview_setting-update
     *
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Interview\Interview_setting\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testInterviewSettingUpdate()
    {
        $this->request->setStage([
            'interview_setting_id' => self::$id,
            'profile_id' => 1,
        ]);

        cradle()->trigger('interview_setting-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('interview_setting_id'));
    }
}
