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
 * @package  Resume
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Resume_EventsTest extends PHPUnit_Framework_TestCase
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
     * resume-create
     *
     * @covers Cradle\Module\Resume\Validator::getCreateErrors
     * @covers Cradle\Module\Resume\Validator::getOptionalErrors
     * @covers Cradle\Module\Resume\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testResumeCreate()
    {
        $this->request->setStage([
            'resume_position' => ,
            'resume_link' => ,
        ]);

        cradle()->trigger('resume-create', $this->request, $this->response);
        $this->assertEquals(, $this->response->getResults('resume_position'));
        $this->assertEquals(, $this->response->getResults('resume_link'));
        self::$id = $this->response->getResults('resume_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * resume-detail
     *
     * @covers Cradle\Module\Resume\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testResumeDetail()
    {
        $this->request->setStage('resume_id', 1);

        cradle()->trigger('resume-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('resume_id'));
    }

    /**
     * resume-remove
     *
     * @covers Cradle\Module\Resume\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Resume\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testResumeRemove()
    {
        $this->request->setStage('resume_id', self::$id);

        cradle()->trigger('resume-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('resume_id'));
    }

    /**
     * resume-restore
     *
     * @covers Cradle\Module\Resume\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Resume\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testResumeRestore()
    {
        $this->request->setStage('resume_id', 581);

        cradle()->trigger('resume-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('resume_id'));
        $this->assertEquals(1, $this->response->getResults('resume_active'));
    }

    /**
     * resume-search
     *
     * @covers Cradle\Module\Resume\Service\SqlService::search
     * @covers Cradle\Module\Resume\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testResumeSearch()
    {
        cradle()->trigger('resume-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'resume_id'));
    }

    /**
     * resume-update
     *
     * @covers Cradle\Module\Resume\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Resume\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testResumeUpdate()
    {
        $this->request->setStage([
            'resume_id' => self::$id,
            'resume_position' => ,
            'resume_link' => ,
        ]);

        cradle()->trigger('resume-update', $this->request, $this->response);
        $this->assertEquals(, $this->response->getResults('resume_position'));
        $this->assertEquals(, $this->response->getResults('resume_link'));
        $this->assertEquals(self::$id, $this->response->getResults('resume_id'));
    }
}
