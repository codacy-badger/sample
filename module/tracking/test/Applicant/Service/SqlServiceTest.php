<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Applicant\Service;

/**
 * SQL service test
 * Applicant Model Test
 *
 * @vendor   Acme
 * @package  Applicant
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Tracking_Applicant_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['applicant_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['applicant_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['applicant_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'applicant_id' => $id,
        ]);

        $this->assertEquals($id, $actual['applicant_id']);
    }

    /**
     * @covers Cradle\Module\Tracking\Applicant\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['applicant_id']);
    }

    /**
     * @covers Cradle\Module\Applicant\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['applicant_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Applicant\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['applicant_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Applicant\Service\SqlService::unlinkProfile
     */
    public function testUnlinkAllProfile()
    {
        $actual = $this->object->unlinkAllProfile(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['applicant_id']);
    }
    

    /**
     * @covers Cradle\Module\Applicant\Service\SqlService::linkForm
     */
    public function testLinkForm()
    {
        $actual = $this->object->linkForm(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['applicant_id']);
        $this->assertEquals(999, $actual['form_id']);
    }

    /**
     * @covers Cradle\Module\Applicant\Service\SqlService::unlinkForm
     */
    public function testUnlinkForm()
    {
        $actual = $this->object->unlinkForm(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['applicant_id']);
        $this->assertEquals(999, $actual['form_id']);
    }

    /**
     * @covers Cradle\Module\Applicant\Service\SqlService::unlinkForm
     */
    public function testUnlinkAllForm()
    {
        $actual = $this->object->unlinkAllForm(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['applicant_id']);
    }
    

    /**
     * @covers Cradle\Module\Applicant\Service\SqlService::linkAnswer
     */
    public function testLinkAnswer()
    {
        $actual = $this->object->linkAnswer(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['applicant_id']);
        $this->assertEquals(999, $actual['answer_id']);
    }

    /**
     * @covers Cradle\Module\Applicant\Service\SqlService::unlinkAnswer
     */
    public function testUnlinkAnswer()
    {
        $actual = $this->object->unlinkAnswer(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['applicant_id']);
        $this->assertEquals(999, $actual['answer_id']);
    }

    /**
     * @covers Cradle\Module\Applicant\Service\SqlService::unlinkAnswer
     */
    public function testUnlinkAllAnswer()
    {
        $actual = $this->object->unlinkAllAnswer(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['applicant_id']);
    }
    
}
