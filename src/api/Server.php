<?php


namespace suframe\proxy\api;

use suframe\core\components\Config;
use suframe\core\components\register\Client as ClientAlias;
use suframe\core\components\swoole\ProcessTools;

class Server extends Base
{

    /**
     * @param $args
     * @return bool
     * @throws \Exception
     */
    public function register($args){
        return \suframe\core\components\register\Server::getInstance()->register($args);
    }

    /**
     * 获取全部服务
     * @return array
     */
    public function get(){
        $config = ClientAlias::getInstance()->reloadServer();
        $servers = $config->get('servers');
        return $servers->toArray();
    }

    /**
     *
     */
    public function syncRpc(){
        return \suframe\core\components\register\Server::getInstance()->buildRpcMeta();
    }

}