<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Oauth\Role\Service;

/**
 * SQL service test
 * Role Model Test
 *
 * @vendor   Acme
 * @package  Role
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Oauth_Role_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'role_name' => 'Test Admin',
            'role_permissions' => json_encode([
                'admin:position:listing',
                'admin:position:create',
                'admin:utm:listing',
                'admin:utm:remove',
                'admin:transaction:listing',
                'admin:transaction:update',
                'admin:transaction:remove',
                'admin:profile:listing',
                'admin:profile:send-claim-email',
                'admin:profile:export',
                'admin:profile:export-csv-format',
                'admin:profile:upload-csv']),
            'role_type' => 'admin'
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['role_id']);
    }

    /**
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'role_id' => $id,
            'role_name' => 'Apple',
        ]);

        $this->assertEquals($id, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\Oauth\Role\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::linkAuth
     */
    public function testLinkAuth()
    {
        $actual = $this->object->linkAuth(2, 5);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(2, $actual['role_id']);
        $this->assertEquals(5, $actual['auth_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::unlinkAuth
     */
    public function testUnlinkAuth()
    {
        $actual = $this->object->unlinkAuth(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['role_id']);
        $this->assertEquals(999, $actual['auth_id']);
    }

    /**
     * @covers Cradle\Module\Role\Service\SqlService::unlinkAuth
     */
    public function testUnlinkAllAuth()
    {
        $actual = $this->object->unlinkAllAuth(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['role_id']);
    }
    
}
