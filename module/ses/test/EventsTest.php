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
 * @package  Ses
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ses_EventsTest extends PHPUnit_Framework_TestCase
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
     * ses-create
     *
     * @covers Cradle\Module\Ses\Validator::getCreateErrors
     * @covers Cradle\Module\Ses\Validator::getOptionalErrors
     * @covers Cradle\Module\Ses\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testSesCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('ses-create', $this->request, $this->response);
        self::$id = $this->response->getResults('ses_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * ses-detail
     *
     * @covers Cradle\Module\Ses\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testSesDetail()
    {
        $this->request->setStage('ses_id', 1);

        cradle()->trigger('ses-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('ses_id'));
    }

    /**
     * ses-remove
     *
     * @covers Cradle\Module\Ses\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Ses\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testSesRemove()
    {
        $this->request->setStage('ses_id', self::$id);

        cradle()->trigger('ses-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('ses_id'));
    }

    /**
     * ses-restore
     *
     * @covers Cradle\Module\Ses\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Ses\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testSesRestore()
    {
        $this->request->setStage('ses_id', 581);

        cradle()->trigger('ses-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('ses_id'));
        $this->assertEquals(1, $this->response->getResults('ses_active'));
    }

    /**
     * ses-search
     *
     * @covers Cradle\Module\Ses\Service\SqlService::search
     * @covers Cradle\Module\Ses\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testSesSearch()
    {
        cradle()->trigger('ses-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'ses_id'));
    }

    /**
     * ses-update
     *
     * @covers Cradle\Module\Ses\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Ses\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testSesUpdate()
    {
        $this->request->setStage([
            'ses_id' => self::$id,
        ]);

        cradle()->trigger('ses-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('ses_id'));
    }
}
