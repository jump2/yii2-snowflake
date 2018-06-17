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

    public $workerNum = 4;

    public $daemonize = 1;

    public $datacenterId = 1;

    protected $server;

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
        $server->on('connect', [$this, 'onConnect']);
        $server->on('receive', [$this, 'onReceive']);
        $server->on('close', [$this, 'onClose']);
        $server->on('shutdown', [$this, 'onShutDown']);
        $server->on('start', [$this, 'onStart']);
        $server->on('managerStart', [$this, 'onManagerStart']);
        $server->on('workerStart', [$this, 'onWorkerStart']);

        return $server;
    }

    protected function createClient()
    {
        $client = Yii::createObject([
            'class' => Client::class,
            'host' => $this->host,
            'port' => $this->port
        ]);

        return $client;
    }

    public function onConnect($server, $fd)
    {
        echo "connection open: {$fd}\n";
    }

    public function onStart($server)
    {
        swoole_set_process_name('Snowflake server(master)');
    }

    public function onWorkerStart($server, $workerId)
    {
        $this->worker = new SnowFlake($workerId, $this->datacenterId);
        if($workerId < $server->setting['worker_num']) {
            swoole_set_process_name('Snowflake server(worker)');
        }
    }

    public function onManagerStart($server)
    {
        swoole_set_process_name('Snowflake server(manager)');
    }

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        switch ($data) {
            case 'NEXT':
                $response = $this->worker->nextId();
                break;
            case 'STOP':
                $server->shutdown();
                $response = 'Snowflake server is shutdown now';
                break;
            case 'RELOAD':
                $server->reload();
                $response = 'Snowflake server is reloading now';
                break;
            case 'STATUS':
                $response = $server->stats();
                break;
            default:
                $response = 'UNKNOWN COMMAND';
                break;
        }
        $server->send($fd, $response);
    }

    public function onClose($server, $fd)
    {
        echo "connection close: {$fd}\n";
    }

    public function onShutDown($server)
    {
        echo 'Snowflake server stop', PHP_EOL;
    }

    /**
     * This method should be called in order to start the server.
     *
     * @return void
     */
    public function run($action)
    {
        return $this->$action();
    }

    protected function start()
    {
        $this->server = $this->createServer();
        $this->server->start();
    }

    protected function stop()
    {
        $client = $this->createClient();
        $client->stop();
    }

    protected function reload()
    {
        $client = $this->createClient();
        $client->reload();
    }

    protected function status()
    {
        $client = $this->createClient();
        return $client->status();
    }
}
