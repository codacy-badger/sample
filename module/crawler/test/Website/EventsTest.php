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
 * @package  Website
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Website_EventsTest extends PHPUnit_Framework_TestCase
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
     * website-create
     *
     * @covers Cradle\Module\Crawler\Website\Validator::getCreateErrors
     * @covers Cradle\Module\Crawler\Website\Validator::getOptionalErrors
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testWebsiteCreate()
    {
        $this->request->setStage([
            'website_name' => 'Acme Inc.',
            'website_root' => 'http://acme.com/',
            'website_start' => 'http://acme.com/start',
            'website_currency' => 'PHP',
            'website_locale' => 'philippines',
        ]);

        cradle()->trigger('website-create', $this->request, $this->response);
        $this->assertEquals('Acme Inc.', $this->response->getResults('website_name'));
        $this->assertEquals('http://acme.com/', $this->response->getResults('website_root'));
        $this->assertEquals('http://acme.com/start', $this->response->getResults('website_start'));
        $this->assertEquals('PHP', $this->response->getResults('website_currency'));
        $this->assertEquals('philippines', $this->response->getResults('website_locale'));
        self::$id = $this->response->getResults('website_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * website-detail
     *
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testWebsiteDetail()
    {
        $this->request->setStage('website_id', 1);

        cradle()->trigger('website-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('website_id'));
    }

    /**
     * website-remove
     *
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testWebsiteRemove()
    {
        $this->request->setStage('website_id', self::$id);

        cradle()->trigger('website-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('website_id'));
    }

    /**
     * website-restore
     *
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testWebsiteRestore()
    {
        $this->request->setStage('website_id', 581);

        cradle()->trigger('website-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('website_id'));
        $this->assertEquals(1, $this->response->getResults('website_active'));
    }

    /**
     * website-search
     *
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::search
     * @covers Cradle\Module\Crawler\Website\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testWebsiteSearch()
    {
        cradle()->trigger('website-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'website_id'));
    }

    /**
     * website-update
     *
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Crawler\Website\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testWebsiteUpdate()
    {
        $this->request->setStage([
            'website_id' => self::$id,
            'website_name' => 'Acme Inc.',
            'website_root' => 'http://acme.com/',
            'website_start' => 'http://acme.com/start',
            'website_currency' => 'PHP',
            'website_locale' => 'philippines',
        ]);

        cradle()->trigger('website-update', $this->request, $this->response);
        $this->assertEquals('Acme Inc.', $this->response->getResults('website_name'));
        $this->assertEquals('http://acme.com/', $this->response->getResults('website_root'));
        $this->assertEquals('http://acme.com/start', $this->response->getResults('website_start'));
        $this->assertEquals('PHP', $this->response->getResults('website_currency'));
        $this->assertEquals('philippines', $this->response->getResults('website_locale'));
        $this->assertEquals(self::$id, $this->response->getResults('website_id'));
    }
}
