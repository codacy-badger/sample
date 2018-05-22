<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Campaign\Service;

/**
 * SQL service test
 * Campaign Model Test
 *
 * @vendor   Acme
 * @package  Campaign
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Campaign_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'campaign_title' => 'test campaign',
            'campaign_medium' => 'email',
            'campaign_source' => 'lead',
            'campaign_audience' => 'seeker',
        ]);


        $id = $this->object->getResource()->getLastInsertedId();

        $this->object->linkTemplate($id, $actual['campaign_id']);

        $this->assertEquals($id, $actual['campaign_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        // $this->assertEquals(1, $actual['campaign_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        // $this->assertEquals(1, $actual['rows'][0]['campaign_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'campaign_id' => $id,
        ]);

        $this->assertEquals($id, $actual['campaign_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['campaign_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::linkTemplate
     */
    public function testLinkTemplate()
    {
        $actual = $this->object->linkTemplate(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['campaign_id']);
        $this->assertEquals(999, $actual['template_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::unlinkTemplate
     */
    public function testUnlinkTemplate()
    {
        $actual = $this->object->unlinkTemplate(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['campaign_id']);
        $this->assertEquals(999, $actual['template_id']);
    }


    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::linkLead
     */
    public function testLinkLead()
    {
        $actual = $this->object->linkLead(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['campaign_id']);
        $this->assertEquals(999, $actual['lead_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::unlinkLead
     */
    public function testUnlinkLead()
    {
        $actual = $this->object->unlinkLead(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['campaign_id']);
        $this->assertEquals(999, $actual['lead_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::unlinkLead
     */
    public function testUnlinkAllLead()
    {
        $actual = $this->object->unlinkAllLead(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['campaign_id']);
    }


    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['campaign_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['campaign_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Campaign\Service\SqlService::unlinkProfile
     */
    public function testUnlinkAllProfile()
    {
        $actual = $this->object->unlinkAllProfile(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['campaign_id']);
    }

}
