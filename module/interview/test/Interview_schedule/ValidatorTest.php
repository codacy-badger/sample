<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Interview\Interview_schedule\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Interview_schedule
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Interview_Interview_schedule_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Interview\Interview_schedule\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
    }

    /**
     * @covers Cradle\Module\Interview\Interview_schedule\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['interview_schedule_id']);
    }
}
