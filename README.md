yii2 snowflake
==============
yii2 snowflake extension, your have to install swoole of php extension

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist jump2/yii2-snowflake "*"
```

or add

```
"jump2/yii2-snowflake": "*"
```

to the require section of your `composer.json` file.


Configuration
-----

First of all , you have to configure the Server class in your console application configuration:

```php
'snowflakeServer' => [
    'class'         => 'Snowflake\Server',
    'host'          => '0.0.0.0',
    'port'          => 5599,
    'workerId'      => 1,
    'datacenterId'  => 1
],
```

and create console controller like  the file below the example directory, then run it for start the snowflake server

Next, you have to configure the Client class in your application configuration:

```
'snowflakeClient' => [
    'class'         => 'Snowflake\Client',
    'host'          => '0.0.0.0',
    'port'          => 5599
],
```

Use below code to generate id

```
<?php
echo \Yii::$app->snowflakeClient->nextId();
?>
```