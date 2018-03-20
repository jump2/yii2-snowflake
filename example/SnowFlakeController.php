<?php
/**
 * User: aaron.woo <707230686@qq.com>
 * Date: 2018/3/14
 * Time: 16:05
 */

namespace app\commands;

use yii\console\Controller;

class SnowFlakeController extends Controller
{
    public function actionServer()
    {
        \Yii::$app->snowflakeServer->run();
    }
}