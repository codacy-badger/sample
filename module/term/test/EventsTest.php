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
 * @package  Term
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Term_EventsTest extends PHPUnit_Framework_TestCase
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
     * term-create
     *
     * @covers Cradle\Module\Term\Validator::getCreateErrors
     * @covers Cradle\Module\Term\Validator::getOptionalErrors
     * @covers Cradle\Module\Term\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testTermCreate()
    {
        $this->request->setStage([
            'term_name' => 'Apple',
        ]);

        cradle()->trigger('term-create', $this->request, $this->response);
        $this->assertEquals('Apple', $this->response->getResults('term_name'));
        self::$id = $this->response->getResults('term_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * term-detail
     *
     * @covers Cradle\Module\Term\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testTermDetail()
    {
        $this->request->setStage('term_id', 1);

        cradle()->trigger('term-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('term_id'));
    }

    /**
     * term-remove
     *
     * @covers Cradle\Module\Term\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Term\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testTermRemove()
    {
        $this->request->setStage('term_id', self::$id);

        cradle()->trigger('term-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('term_id'));
    }

    /**
     * term-restore
     *
     * @covers Cradle\Module\Term\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Term\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testTermRestore()
    {
        $this->request->setStage('term_id', 581);

        cradle()->trigger('term-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('term_id'));
        $this->assertEquals(1, $this->response->getResults('term_active'));
    }

    /**
     * term-search
     *
     * @covers Cradle\Module\Term\Service\SqlService::search
     * @covers Cradle\Module\Term\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testTermSearch()
    {
        cradle()->trigger('term-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'term_id'));
    }

    /**
     * term-update
     *
     * @covers Cradle\Module\Term\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Term\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testTermUpdate()
    {
        $this->request->setStage([
            'term_id' => self::$id,
            'term_name' => 'Apple',
        ]);

        cradle()->trigger('term-update', $this->request, $this->response);
        $this->assertEquals('Apple', $this->response->getResults('term_name'));
        $this->assertEquals(self::$id, $this->response->getResults('term_id'));
    }
}
