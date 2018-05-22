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
 * @package  Education
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracer_Education_EventsTest extends PHPUnit_Framework_TestCase
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
     * education-create
     *
     * @covers Cradle\Module\Tracer\Education\Validator::getCreateErrors
     * @covers Cradle\Module\Tracer\Education\Validator::getOptionalErrors
     * @covers Cradle\Module\Tracer\Education\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testEducationCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('education-create', $this->request, $this->response);
        self::$id = $this->response->getResults('education_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * education-detail
     *
     * @covers Cradle\Module\Tracer\Education\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testEducationDetail()
    {
        $this->request->setStage('education_id', 1);

        cradle()->trigger('education-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('education_id'));
    }

    /**
     * education-remove
     *
     * @covers Cradle\Module\Tracer\Education\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Education\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testEducationRemove()
    {
        $this->request->setStage('education_id', self::$id);

        cradle()->trigger('education-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('education_id'));
    }

    /**
     * education-restore
     *
     * @covers Cradle\Module\Tracer\Education\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Education\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testEducationRestore()
    {
        $this->request->setStage('education_id', 581);

        cradle()->trigger('education-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('education_id'));
        $this->assertEquals(1, $this->response->getResults('education_active'));
    }

    /**
     * education-search
     *
     * @covers Cradle\Module\Tracer\Education\Service\SqlService::search
     * @covers Cradle\Module\Tracer\Education\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testEducationSearch()
    {
        cradle()->trigger('education-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'education_id'));
    }

    /**
     * education-update
     *
     * @covers Cradle\Module\Tracer\Education\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Education\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testEducationUpdate()
    {
        $this->request->setStage([
            'education_id' => self::$id,
        ]);

        cradle()->trigger('education-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('education_id'));
    }
}
