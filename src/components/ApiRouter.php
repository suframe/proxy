<?php

namespace suframe\proxy\components;

use Exception;
use suframe\core\traits\Singleton;
use Swoole\Http\Request;

class ApiRouter
{
    use Singleton;

    /**
     * 服务代理转发
     * @param Request $request
     * @return false|string
     * @throws Exception
     */
    public function dispatch(Request $request)
    {
        $path = $request->server['path_info'];
        $path = ltrim($path, '/');
        $apiName = explode('/', $path);
        if ($apiName[0] !== 'summer') {
            throw new Exception('path error');
        }
        array_shift($apiName);
        $methodName = array_pop($apiName);
        $className = array_pop($apiName);
        $className = ucfirst($className);
        $apiName[] = $className;
        $apiName = implode('\\', $apiName);
        $apiClass = '\suframe\proxy\api\\' . $apiName;

        if (!class_exists($apiClass)) {
            throw new Exception('api class not found');
        }
        $api = new $apiClass;
        if (!method_exists($api, $methodName)) {
            throw new Exception('api method not found');
        }
        $args = $request->post ?: [];
        $rs = $api->$methodName($args);
        return $rs;
    }

}
