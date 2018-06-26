<?php
/**
 * User: aaron.woo <707230686@qq.com>
 * Date: 2018/6/26
 * Time: 19:27
 */

namespace Snowflake;

use yii\base\Component;

abstract class BaseServer extends Component
{
    /** @var IdWorker */
    protected $worker;

    /** @var integer */
    public $port;

    public $host;

    public $workerNum = 4;

    public $daemonize = 1;

    public $datacenterId = 1;

    public $serverName = 'SnowflakeServer';

    protected $server;
    /**
     * Create Server
     * @return \Swoole\Server
     */
    abstract public function createServer();

    public function onStart($server)
    {
        swoole_set_process_name($this->getServerMasterName());
    }

    public function onWorkerStart($server, $workerId)
    {
        $this->worker = new SnowFlake($workerId, $this->datacenterId);
        if($workerId < $server->setting['worker_num']) {
            swoole_set_process_name($this->getServerWorkerName());
        }
    }

    public function onManagerStart($server)
    {
        swoole_set_process_name($this->getServerManagerName());
    }

    protected function getServerMasterName()
    {
        return $this->serverName . '-Master';
    }

    protected function getServerManagerName()
    {
        return $this->serverName . '-Manager';
    }

    protected function getServerWorkerName()
    {
        return $this->serverName . '-Worker';
    }

    public function __call($name, $params)
    {
        throw new \Exception('Unknown param: ' . $name);
    }

    /**
     * This method should be called in order to start|stop|reload the server.
     *
     * @return void
     */
    public function run($action)
    {
        return $this->$action();
    }

    protected function stop()
    {
        $masterPid = exec('ps -ef | grep ' . $this->getServerMasterName() . " | grep -v 'grep ' | awk '{print $2}'");
        if (empty($masterPid)) {
            throw new \Exception('Server ' . $this->serverName . ' Not Run');
        }

        // Send stop signal to master process.
        $masterPid && posix_kill($masterPid, SIGTERM);
        // Timeout.
        $timeout = 40;
        $startTime = time();
        // Check master process is still alive?
        while (1) {
            $master_is_alive = $masterPid && posix_kill($masterPid, 0);
            if ($master_is_alive) {
                // Timeout?
                if (time() - $startTime >= $timeout) {
                    throw new \Exception('Server ' . $this->serverName . 'Stop Fail');
                }
                // Waiting amoment.
                usleep(10000);
                continue;
            }
            // Stop success.
            break;
        }
    }

    protected function reload()
    {
        $masterPid = exec('ps -ef | grep ' . $this->getServerMasterName() . " | grep -v 'grep ' | awk '{print $2}'");
        $managerPid = exec('ps -ef | grep ' . $this->getServerManagerName() . " | grep -v 'grep ' | awk '{print $2}'");
        if (empty($masterPid)) {
            throw new \Exception('Server ' . $this->serverName . ' Not Run');
        }
        posix_kill($managerPid, SIGUSR1);
    }

    protected function restart()
    {
        $masterPid = exec('ps -ef | grep ' . $this->getServerMasterName() . " | grep -v 'grep ' | awk '{print $2}'");
        if (empty($masterPid)) {
            throw new \Exception('Server ' . $this->serverName . ' Not Run');
        }
        $this->stop();
        $this->start();
    }

    public function start()
    {
        $masterPid = exec('ps -ef | grep ' . $this->getServerMasterName() . " | grep -v 'grep ' | awk '{print $2}'");
        if (!empty($masterPid)) {
            throw new \Exception($this->serverName . ' Server Already Running');
        }
        $this->server = $this->createServer();
        $this->server->start();
    }
}