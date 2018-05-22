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
 * @package  Research
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Research_EventsTest extends PHPUnit_Framework_TestCase
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
     * research-create
     *
     * @covers Cradle\Module\Research\Validator::getCreateErrors
     * @covers Cradle\Module\Research\Validator::getOptionalErrors
     * @covers Cradle\Module\Research\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testResearchCreate()
    {
        $this->request->setStage([
        ]);

        cradle()->trigger('research-create', $this->request, $this->response);
        self::$id = $this->response->getResults('research_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * research-detail
     *
     * @covers Cradle\Module\Research\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testResearchDetail()
    {
        $this->request->setStage('research_id', 1);

        cradle()->trigger('research-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('research_id'));
    }

    /**
     * research-remove
     *
     * @covers Cradle\Module\Research\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Research\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testResearchRemove()
    {
        $this->request->setStage('research_id', self::$id);

        cradle()->trigger('research-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('research_id'));
    }

    /**
     * research-restore
     *
     * @covers Cradle\Module\Research\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Research\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testResearchRestore()
    {
        $this->request->setStage('research_id', 581);

        cradle()->trigger('research-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('research_id'));
        $this->assertEquals(1, $this->response->getResults('research_active'));
    }

    /**
     * research-search
     *
     * @covers Cradle\Module\Research\Service\SqlService::search
     * @covers Cradle\Module\Research\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testResearchSearch()
    {
        cradle()->trigger('research-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'research_id'));
    }

    /**
     * research-update
     *
     * @covers Cradle\Module\Research\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Research\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testResearchUpdate()
    {
        $this->request->setStage([
            'research_id' => self::$id,
        ]);

        cradle()->trigger('research-update', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('research_id'));
    }
}
