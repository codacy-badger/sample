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
 * @package  Experience
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracer_Experience_EventsTest extends PHPUnit_Framework_TestCase
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
     * experience-create
     *
     * @covers Cradle\Module\Tracer\Experience\Validator::getCreateErrors
     * @covers Cradle\Module\Tracer\Experience\Validator::getOptionalErrors
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testExperienceCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('experience-create', $this->request, $this->response);
        self::$id = $this->response->getResults('experience_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * experience-detail
     *
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testExperienceDetail()
    {
        $this->request->setStage('experience_id', 1);

        cradle()->trigger('experience-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('experience_id'));
    }

    /**
     * experience-remove
     *
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testExperienceRemove()
    {
        $this->request->setStage('experience_id', self::$id);

        cradle()->trigger('experience-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('experience_id'));
    }

    /**
     * experience-restore
     *
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testExperienceRestore()
    {
        $this->request->setStage('experience_id', 581);

        cradle()->trigger('experience-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('experience_id'));
        $this->assertEquals(1, $this->response->getResults('experience_active'));
    }

    /**
     * experience-search
     *
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::search
     * @covers Cradle\Module\Tracer\Experience\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testExperienceSearch()
    {
        cradle()->trigger('experience-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'experience_id'));
    }

    /**
     * experience-update
     *
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Tracer\Experience\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testExperienceUpdate()
    {
        $this->request->setStage([
            'experience_id' => self::$id,
        ]);

        cradle()->trigger('experience-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('experience_id'));
    }
}
