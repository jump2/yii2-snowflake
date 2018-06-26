<?php
/**
 * User: aaron.woo <707230686@qq.com>
 * Date: 2018/6/26
 * Time: 19:36
 */

namespace Snowflake;

use Yii;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class HttpServer extends BaseServer
{
    public function createServer()
    {
        $server = new Server($this->host, $this->port);
        $server->set([
            'worker_num' => $this->workerNum,
            'daemonize' => $this->daemonize,
        ]);
        $server->on('request', [$this, 'onRequest']);
        $server->on('start', [$this, 'onStart']);
        $server->on('managerStart', [$this, 'onManagerStart']);
        $server->on('workerStart', [$this, 'onWorkerStart']);

        return $server;
    }

    public function onRequest(Request $request, Response $response)
    {
        $id = $this->worker->nextId();
        $response->end($id);
    }
}