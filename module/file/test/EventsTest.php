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
 * @package  File
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_File_EventsTest extends PHPUnit_Framework_TestCase
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
     * file-create
     *
     * @covers Cradle\Module\File\Validator::getCreateErrors
     * @covers Cradle\Module\File\Validator::getOptionalErrors
     * @covers Cradle\Module\File\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testFileCreate()
    {
        $this->request->setStage([
            'comment_id' => 1,
        ]);

        cradle()->trigger('file-create', $this->request, $this->response);
        self::$id = $this->response->getResults('file_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * file-detail
     *
     * @covers Cradle\Module\File\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testFileDetail()
    {
        $this->request->setStage('file_id', 1);

        cradle()->trigger('file-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('file_id'));
    }

    /**
     * file-remove
     *
     * @covers Cradle\Module\File\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\File\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testFileRemove()
    {
        $this->request->setStage('file_id', self::$id);

        cradle()->trigger('file-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('file_id'));
    }

    /**
     * file-restore
     *
     * @covers Cradle\Module\File\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\File\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testFileRestore()
    {
        $this->request->setStage('file_id', 581);

        cradle()->trigger('file-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('file_id'));
        $this->assertEquals(1, $this->response->getResults('file_active'));
    }

    /**
     * file-search
     *
     * @covers Cradle\Module\File\Service\SqlService::search
     * @covers Cradle\Module\File\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testFileSearch()
    {
        cradle()->trigger('file-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'file_id'));
    }

    /**
     * file-update
     *
     * @covers Cradle\Module\File\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\File\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testFileUpdate()
    {
        $this->request->setStage([
            'file_id' => self::$id,
            'comment_id' => 1,
        ]);

        cradle()->trigger('file-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('file_id'));
    }
}
