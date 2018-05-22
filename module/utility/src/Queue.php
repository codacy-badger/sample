<?php //-->
/**
 * This file is part of the Cradle PHP Library.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Utility;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Typical model create action steps
 *
 * @vendor   Cradle
 * @package  Framework
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Queue
{
    public $connection = null;
    public $exchange = 'exchange';
    public $data = array();

    public function __construct()
    {
        $config = cradle('global')->config('services')['rabbitmq-main'];

        if ($config) {
            $this->connection = new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['user'],
                $config['pass']
            );
        }
    }

    public function setExchange($exchange = 'exchange')
    {
        $this->exchange = $exchange;
        return $this;
    }

    public function setData($data = array())
    {
        $this->data = $data;
        return $this;
    }

    public function subscribe($callback)
    {
        if (!$this->connection) {
            return;
        }

        $channel = $this->connection->channel();
        $channel->exchange_declare($this->exchange, 'fanout', false, false, false);

        list($queue, ,) = $channel->queue_declare("", false, false, true, false);
        $channel->queue_bind($queue, $this->exchange);
        $channel->basic_consume($queue, '', false, true, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }

    public function publish()
    {
        if (!$this->connection) {
            return;
        }

        $channel = $this->connection->channel();

        $channel->exchange_declare($this->exchange, 'fanout', false, false, false);
        $message = new AMQPMessage(json_encode($this->data), array(
                'content_type' => 'text/plain',
                'delivery_mode' => 2 // make message persistent
            ));

        $channel->basic_publish($message, $this->exchange);

        $channel->close();
        $this->connection->close();
    }
}
