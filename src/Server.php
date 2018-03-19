<?php

namespace Snowflake;

use Yii;
use yii\base\Component;

/**
 * Snowflake Swoole server
 *
 * Creates a TCP server that accepts plain text commands.
 *
 * Receives a command "NEXT" to generate and return a new ID.
 *
 */
class Server extends Component
{
    /** @var IdWorker */
    protected $worker;

    /** @var integer */
    public $port;

    public $host;

    public $workerId;

    public $datacenterId;

    /**
     * Snowflake Server init
     */
    public function init()
    {
        $this->worker = new SnowFlake($this->workerId, $this->datacenterId);
    }

    /**
     * This method should be called in order to start the server.
     *
     * @return void
     */
    public function run()
    {
        $server = new \Swoole\Server($this->host, $this->port);
        $server->on('connect', function ($server, $fd){
            echo "connection open: {$fd}\n";
        });
        $server->on('receive', function ($server, $fd, $reactor_id, $data) {
            switch ($data) {
                case 'NEXT':
                    $response = $this->worker->nextId();
                    break;
                default:
                    $response = 'UNKNOWN COMMAND';
                    break;
            }
            $server->send($fd, $response);
        });
        $server->on('close', function ($server, $fd) {
            echo "connection close: {$fd}\n";
        });
        $server->start();
    }
}