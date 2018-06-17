<?php
/**
 * User: aaron.woo <707230686@qq.com>
 * Date: 2018/3/14
 * Time: 16:05
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class SnowFlakeController extends Controller
{
    /**
     * ID生成器服务
     *
     * 事例如下
     *
     * ```
     * yii snow-flake/server start  # 启动ID生成器服务
     * yii snow-flake/server stop   # 停止ID生成器服务
     * yii snow-flake/server reload # 重载ID生成器服务
     * ```
     * @param string $action #取值范围[start, stop, reload], 默认start
     * @return int
     */
    public function actionServer($action = 'start')
    {
        try {
            \Yii::$app->snowFlakeServer->run($action);
            $this->stdOutput($action, 1);
        } catch (\Exception $e) {
            $this->stderr($e->getMessage() . PHP_EOL, Console::FG_RED);
            $this->stdOutput($action, 0);
        }

        return ExitCode::OK;
    }

    protected function stdOutput($action, $type)
    {
        switch ($action) {
            case 'start':
                $message = $type ? 'Snowflake server has started successfully.' : 'Snowflake server has started failure.';
                break;
            case 'reload':
                $message = $type ? 'Snowflake server has reloaded successfully.' : 'Snowflake server has reloaded failure.';
                break;
            case 'stop':
                $message = $type ? 'Snowflake server has stop successfully.' : 'Snowflake server has stop failure.';
                break;
            default:
                $message = 'Unknown param: ' . $action;
                $type = 0;
        }

        if ($type) {
            $this->stdout($message . PHP_EOL, Console::FG_GREEN);
        } else {
            $this->stderr($message . PHP_EOL, Console::FG_RED);
        }
    }

    public function actionClient()
    {
        for ($i = 0; $i < 10; $i++) {
            echo \Yii::$app->snowFlakeClient->nextId(), PHP_EOL;
        }
    }
}
