<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Webpage\Service;

use Cradle\Module\Crawler\Webpage\Service\SqlService;
use Cradle\Module\Utility\Service\NoopService;

/**
 * Service layer test
 *
 * @vendor   Acme
 * @package  Webpage
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Crawler_Webpage_ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Crawler\Webpage\Service::get
     */
    public function testGet()
    {
        $actual = Service::get('sql');
        $this->assertTrue($actual instanceof SqlService || $actual instanceof NoopService);
    }
}
