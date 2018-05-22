<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Lead\Service;

use Cradle\Module\Lead\Service\SqlService;
use Cradle\Module\Lead\Service\RedisService;
use Cradle\Module\Lead\Service\ElasticService;
use Cradle\Module\Utility\Service\NoopService;

/**
 * Service layer test
 *
 * @vendor   Acme
 * @package  Lead
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Lead_ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Lead\Service::get
     */
    public function testGet()
    {
        $actual = Service::get('sql');
        $this->assertTrue($actual instanceof SqlService || $actual instanceof NoopService);

        $actual = Service::get('redis');
        $this->assertTrue($actual instanceof RedisService || $actual instanceof NoopService);

        $actual = Service::get('elastic');
        $this->assertTrue($actual instanceof ElasticService || $actual instanceof NoopService);
    }
}
