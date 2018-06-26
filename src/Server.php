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
class Server extends BaseServer
{
    /**
     * Create Server
     * @return \Swoole\Server
     */
    public function createServer()
    {
        $server = new \Swoole\Server($this->host, $this->port);
        $server->set([
            'worker_num' => $this->workerNum,
            'daemonize' => $this->daemonize,
        ]);
        $server->on('receive', [$this, 'onReceive']);
        $server->on('start', [$this, 'onStart']);
        $server->on('managerStart', [$this, 'onManagerStart']);
        $server->on('workerStart', [$this, 'onWorkerStart']);

        return $server;
    }

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        switch ($data) {
            case 'NEXT':
                $response = $this->worker->nextId();
                break;
            default:
                $response = 'UNKNOWN COMMAND';
                break;
        }
        $server->send($fd, $response);
    }
}
