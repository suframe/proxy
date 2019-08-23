<?php
/**
 * User: qian
 * Date: 2019/6/5 13:17
 */

namespace suframe\proxy\events;

use suframe\core\components\Config;
use suframe\core\components\log\Log;
use suframe\core\components\log\LogConfig;
use suframe\core\components\rpc\SRpc;
use Swoole\Http\Request;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;

class LogListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    protected $logRoute;
    /**
     * 注册事件
     * @param EventManagerInterface $events
     * @param int $priority
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->logRoute = Config::getInstance()->get('sapps.log');
        if ($this->logRoute) {
            $this->listeners[] = $events->attach('http.request.after', [$this, 'requestAfter'], $priority);
        }
    }

    public function requestAfter(EventInterface $e)
    {
        /** @var Request $request */
        $request = $e->getParam('request');
        $status = $e->getParam('status');
        $info = [
            'status' => $status,
            'request_method' => $request->server['request_method'] ?? 'get',
            'remote_addr' => $request->server['remote_addr'] ?? '',
            'server_port' => $request->server['server_port'] ?? 0,
            'request_uri' => $request->server['request_uri'] ?? '',
            'query_string' => $request->server['query_string'] ?? '',
            'request_time' => $request->server['request_time'] ?? 0,
            'x_request_id' => $request->get['x_request_id'],
        ];
        Log::getInstance()->request($info, 'proxy');
    }

}