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
 * @package  Applicant
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Applicant_EventsTest extends PHPUnit_Framework_TestCase
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
     * applicant-create
     *
     * @covers Cradle\Module\Tracking\Applicant\Validator::getCreateErrors
     * @covers Cradle\Module\Tracking\Applicant\Validator::getOptionalErrors
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testApplicantCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('applicant-create', $this->request, $this->response);
        self::$id = $this->response->getResults('applicant_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * applicant-detail
     *
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testApplicantDetail()
    {
        $this->request->setStage('applicant_id', 1);

        cradle()->trigger('applicant-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('applicant_id'));
    }

    /**
     * applicant-remove
     *
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testApplicantRemove()
    {
        $this->request->setStage('applicant_id', self::$id);

        cradle()->trigger('applicant-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('applicant_id'));
    }

    /**
     * applicant-restore
     *
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testApplicantRestore()
    {
        $this->request->setStage('applicant_id', 581);

        cradle()->trigger('applicant-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('applicant_id'));
        $this->assertEquals(1, $this->response->getResults('applicant_active'));
    }

    /**
     * applicant-search
     *
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::search
     * @covers Cradle\Module\Tracking\Applicant\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testApplicantSearch()
    {
        cradle()->trigger('applicant-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'applicant_id'));
    }

    /**
     * applicant-update
     *
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testApplicantUpdate()
    {
        $this->request->setStage([
            'applicant_id' => self::$id,
        ]);

        cradle()->trigger('applicant-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('applicant_id'));
    }
}
