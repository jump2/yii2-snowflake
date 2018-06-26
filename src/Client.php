<?php

namespace Snowflake;

use yii\base\Component;

/**
 * Snowflake client
 */
class Client extends Component
{
    protected $client;

    protected $connect;

    public $host;

    public $port;

    /** @var int 连接重试次数 */
    public $maxRetry = 1;

    /**
     * 实例化\Swoole\Client
     */
    public function init()
    {
        $this->client = new \Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
    }

    /**
     * TCP建立连接
     *
     * @return integer
     * @throws ConnectException
     */
    protected function connect()
    {
        if (!$this->connect) {
            for ($retry = 0; $retry <= $this->maxRetry; $retry++) {
                try {
                    $this->connect = $this->client->connect($this->host, $this->port);
                    return $this->connect;
                } catch (\Exception $e) {
                    if ($retry >= $this->maxRetry) {
                        throw new ConnectException($e->getMessage());
                    } else {
                        $this->client->close();
                    }
                }
            }
        } else {
            return $this->connect;
        }
    }

    /**
     * 获取ID
     *
     * @return boolean|integer
     * @throws
     */
    public function nextId()
    {
        if ($this->connect()) {
            $this->client->send('NEXT');
            return $this->client->recv();
        } else {
            throw new ConnectException('Connect to the snowflake server failed');
        }
    }
}
