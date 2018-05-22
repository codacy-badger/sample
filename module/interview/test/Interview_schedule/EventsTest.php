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
 * @package  Interview_schedule
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Interview_Interview_schedule_EventsTest extends PHPUnit_Framework_TestCase
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
     * interview_schedule-create
     *
     * @covers Cradle\Module\Interview\Interview_schedule\Validator::getCreateErrors
     * @covers Cradle\Module\Interview\Interview_schedule\Validator::getOptionalErrors
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testInterviewScheduleCreate()
    {
        $this->request->setStage([
            'interview_setting_id' => 1,
            'profile_id' => 1,
            'post_id' => 1,
        ]);

        cradle()->trigger('interview_schedule-create', $this->request, $this->response);
        self::$id = $this->response->getResults('interview_schedule_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * interview_schedule-detail
     *
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testInterviewScheduleDetail()
    {
        $this->request->setStage('interview_schedule_id', 1);

        cradle()->trigger('interview_schedule-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('interview_schedule_id'));
    }

    /**
     * interview_schedule-remove
     *
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testInterviewScheduleRemove()
    {
        $this->request->setStage('interview_schedule_id', self::$id);

        cradle()->trigger('interview_schedule-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('interview_schedule_id'));
    }

    /**
     * interview_schedule-restore
     *
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testInterviewScheduleRestore()
    {
        $this->request->setStage('interview_schedule_id', 581);

        cradle()->trigger('interview_schedule-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('interview_schedule_id'));
        $this->assertEquals(1, $this->response->getResults('interview_schedule_active'));
    }

    /**
     * interview_schedule-search
     *
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::search
     * @covers Cradle\Module\Interview\Interview_schedule\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testInterviewScheduleSearch()
    {
        cradle()->trigger('interview_schedule-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'interview_schedule_id'));
    }

    /**
     * interview_schedule-update
     *
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Interview\Interview_schedule\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testInterviewScheduleUpdate()
    {
        $this->request->setStage([
            'interview_schedule_id' => self::$id,
            'interview_setting_id' => 1,
            'profile_id' => 1,
            'post_id' => 1,
        ]);

        cradle()->trigger('interview_schedule-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('interview_schedule_id'));
    }
}
