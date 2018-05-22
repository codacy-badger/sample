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
 * @package  Webpage
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Webpage_EventsTest extends PHPUnit_Framework_TestCase
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
     * webpage-create
     *
     * @covers Cradle\Module\Crawler\Webpage\Validator::getCreateErrors
     * @covers Cradle\Module\Crawler\Webpage\Validator::getOptionalErrors
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testWebpageCreate()
    {
        $this->request->setStage([
            'webpage_root' => 'Foobar Title',
            'webpage_link' => 'http://google.com/',
            'webpage_type' => 'detail',
        ]);

        cradle()->trigger('webpage-create', $this->request, $this->response);
        $this->assertEquals('Foobar Title', $this->response->getResults('webpage_root'));
        $this->assertEquals('http://google.com/', $this->response->getResults('webpage_link'));
        $this->assertEquals('detail', $this->response->getResults('webpage_type'));
        self::$id = $this->response->getResults('webpage_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * webpage-detail
     *
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testWebpageDetail()
    {
        $this->request->setStage('webpage_id', 1);

        cradle()->trigger('webpage-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('webpage_id'));
    }

    /**
     * webpage-remove
     *
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testWebpageRemove()
    {
        $this->request->setStage('webpage_id', self::$id);

        cradle()->trigger('webpage-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('webpage_id'));
    }

    /**
     * webpage-search
     *
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::search
     * @covers Cradle\Module\Crawler\Webpage\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testWebpageSearch()
    {
        cradle()->trigger('webpage-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'webpage_id'));
    }

    /**
     * webpage-update
     *
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Crawler\Webpage\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testWebpageUpdate()
    {
        $this->request->setStage([
            'webpage_id' => self::$id,
            'webpage_root' => 'Foobar Title',
            'webpage_link' => 'http://google.com/',
            'webpage_type' => 'detail',
        ]);

        cradle()->trigger('webpage-update', $this->request, $this->response);
        $this->assertEquals('Foobar Title', $this->response->getResults('webpage_root'));
        $this->assertEquals('http://google.com/', $this->response->getResults('webpage_link'));
        $this->assertEquals('detail', $this->response->getResults('webpage_type'));
        $this->assertEquals(self::$id, $this->response->getResults('webpage_id'));
    }
}
