<?php

namespace suframe\proxy\api;

use suframe\core\components\register\Server;

/**
 * 定时器接口
 * Class ServersTimer
 * @package suframe\register\api
 */
class ServersTimer extends Base
{

    protected $checkTimer;

    /**
     * 启动
     * @return bool
     */
    public function start()
    {
        return Server::getInstance()->createTimer();
    }

    /**
     * 检查
     * @return array
     */
    public function check()
    {
        return Server::getInstance()->checkTimer();
    }

    /**
     * 清除定时器
     * @return bool
     */
    public function clear()
    {
        return Server::getInstance()->clearTimer();
    }
}