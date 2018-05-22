<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\History\Service;

/**
 * SQL service test
 * History Model Test
 *
 * @vendor   Acme
 * @package  History
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_History_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\History\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'history_id' => $id,
        ]);

        $this->assertEquals($id, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['history_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkProfile
     */
    public function testUnlinkAllProfile()
    {
        $actual = $this->object->unlinkAllProfile(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkBlog
     */
    public function testLinkBlog()
    {
        $actual = $this->object->linkBlog(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['blog_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkBlog
     */
    public function testUnlinkBlog()
    {
        $actual = $this->object->unlinkBlog(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['blog_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkBlog
     */
    public function testUnlinkAllBlog()
    {
        $actual = $this->object->unlinkAllBlog(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkFeature
     */
    public function testLinkFeature()
    {
        $actual = $this->object->linkFeature(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['feature_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkFeature
     */
    public function testUnlinkFeature()
    {
        $actual = $this->object->unlinkFeature(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['feature_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkFeature
     */
    public function testUnlinkAllFeature()
    {
        $actual = $this->object->unlinkAllFeature(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkPosition
     */
    public function testLinkPosition()
    {
        $actual = $this->object->linkPosition(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['position_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkPosition
     */
    public function testUnlinkPosition()
    {
        $actual = $this->object->unlinkPosition(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['position_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkPosition
     */
    public function testUnlinkAllPosition()
    {
        $actual = $this->object->unlinkAllPosition(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkPost
     */
    public function testLinkPost()
    {
        $actual = $this->object->linkPost(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['post_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkPost
     */
    public function testUnlinkPost()
    {
        $actual = $this->object->unlinkPost(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['post_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkPost
     */
    public function testUnlinkAllPost()
    {
        $actual = $this->object->unlinkAllPost(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkResearch
     */
    public function testLinkResearch()
    {
        $actual = $this->object->linkResearch(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['research_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkResearch
     */
    public function testUnlinkResearch()
    {
        $actual = $this->object->unlinkResearch(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['research_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkResearch
     */
    public function testUnlinkAllResearch()
    {
        $actual = $this->object->unlinkAllResearch(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkRole
     */
    public function testLinkRole()
    {
        $actual = $this->object->linkRole(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkRole
     */
    public function testUnlinkRole()
    {
        $actual = $this->object->unlinkRole(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['role_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkRole
     */
    public function testUnlinkAllRole()
    {
        $actual = $this->object->unlinkAllRole(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkService
     */
    public function testLinkService()
    {
        $actual = $this->object->linkService(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['service_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkService
     */
    public function testUnlinkService()
    {
        $actual = $this->object->unlinkService(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['service_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkService
     */
    public function testUnlinkAllService()
    {
        $actual = $this->object->unlinkAllService(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkTransaction
     */
    public function testLinkTransaction()
    {
        $actual = $this->object->linkTransaction(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['transaction_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkTransaction
     */
    public function testUnlinkTransaction()
    {
        $actual = $this->object->unlinkTransaction(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['transaction_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkTransaction
     */
    public function testUnlinkAllTransaction()
    {
        $actual = $this->object->unlinkAllTransaction(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    

    /**
     * @covers Cradle\Module\History\Service\SqlService::linkUtm
     */
    public function testLinkUtm()
    {
        $actual = $this->object->linkUtm(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['utm_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkUtm
     */
    public function testUnlinkUtm()
    {
        $actual = $this->object->unlinkUtm(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
        $this->assertEquals(999, $actual['utm_id']);
    }

    /**
     * @covers Cradle\Module\History\Service\SqlService::unlinkUtm
     */
    public function testUnlinkAllUtm()
    {
        $actual = $this->object->unlinkAllUtm(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['history_id']);
    }
    
}
