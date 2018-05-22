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
 * @package  School
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_School_EventsTest extends PHPUnit_Framework_TestCase
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
     * school-create
     *
     * @covers Cradle\Module\School\Validator::getCreateErrors
     * @covers Cradle\Module\School\Validator::getOptionalErrors
     * @covers Cradle\Module\School\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testSchoolCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('school-create', $this->request, $this->response);
        self::$id = $this->response->getResults('school_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * school-detail
     *
     * @covers Cradle\Module\School\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testSchoolDetail()
    {
        $this->request->setStage('school_id', 1);

        cradle()->trigger('school-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('school_id'));
    }

    /**
     * school-remove
     *
     * @covers Cradle\Module\School\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\School\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testSchoolRemove()
    {
        $this->request->setStage('school_id', self::$id);

        cradle()->trigger('school-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('school_id'));
    }

    /**
     * school-restore
     *
     * @covers Cradle\Module\School\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\School\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testSchoolRestore()
    {
        $this->request->setStage('school_id', 581);

        cradle()->trigger('school-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('school_id'));
        $this->assertEquals(1, $this->response->getResults('school_active'));
    }

    /**
     * school-search
     *
     * @covers Cradle\Module\School\Service\SqlService::search
     * @covers Cradle\Module\School\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testSchoolSearch()
    {
        cradle()->trigger('school-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'school_id'));
    }

    /**
     * school-update
     *
     * @covers Cradle\Module\School\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\School\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testSchoolUpdate()
    {
        $this->request->setStage([
            'school_id' => self::$id,
        ]);

        cradle()->trigger('school-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('school_id'));
    }
}
