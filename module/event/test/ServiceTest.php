<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Event\Service;

use Cradle\Module\Event\Service\SqlService;
use Cradle\Module\Event\Service\RedisService;
use Cradle\Module\Event\Service\ElasticService;
use Cradle\Module\Utility\Service\NoopService;

/**
 * Service layer test
 *
 * @vendor   Acme
 * @package  Event
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Event_ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Event\Service::get
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
