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
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Oauth_Role_EventsTest extends PHPUnit_Framework_TestCase
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
     * role-create
     *
     * @covers Cradle\Module\Oauth\Role\Validator::getCreateErrors
     * @covers Cradle\Module\Oauth\Role\Validator::getOptionalErrors
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testRoleCreate()
    {
        $this->request->setStage([
            'role_name' => 'Apple',
        ]);

        cradle()->trigger('role-create', $this->request, $this->response);
        $this->assertEquals('Apple', $this->response->getResults('role_name'));
        self::$id = $this->response->getResults('role_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * role-detail
     *
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testRoleDetail()
    {
        $this->request->setStage('role_id', 1);

        cradle()->trigger('role-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('role_id'));
    }

    /**
     * role-remove
     *
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testRoleRemove()
    {
        $this->request->setStage('role_id', self::$id);

        cradle()->trigger('role-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('role_id'));
    }

    /**
     * role-restore
     *
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testRoleRestore()
    {
        $this->request->setStage('role_id', 581);

        cradle()->trigger('role-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('role_id'));
        $this->assertEquals(1, $this->response->getResults('role_active'));
    }

    /**
     * role-search
     *
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::search
     * @covers Cradle\Module\Oauth\Role\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testRoleSearch()
    {
        cradle()->trigger('role-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'role_id'));
    }

    /**
     * role-update
     *
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testRoleUpdate()
    {
        $this->request->setStage([
            'role_id' => self::$id,
            'role_name' => 'Apple',
        ]);

        cradle()->trigger('role-update', $this->request, $this->response);
        $this->assertEquals('Apple', $this->response->getResults('role_name'));
        $this->assertEquals(self::$id, $this->response->getResults('role_id'));
    }
}
