<?php

namespace suframe\proxy\driver;

use suframe\core\components\Config;
use suframe\core\components\console\SymfonyStyle;
use suframe\core\components\event\EventManager;
use suframe\core\components\net\http\Out;
use suframe\core\components\net\http\Server;
use suframe\core\components\net\http\Proxy;
use suframe\core\components\register\Timer as TimerAlias;
use suframe\core\traits\Singleton;
use suframe\proxy\components\ApiRouter;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HttpDriver
{
    use Singleton;

    protected $config = [];
    /**
     * @var SymfonyStyle
     */
    protected $io;
    /**
     * @var Proxy
     */
    protected $proxy;
    protected $registerPort;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $config = Config::getInstance();
        $http = new Server();
        $this->config = $config->get('tcp')->toArray();
        //守护进程运行
        if (true === $input->hasParameterOption(['--daemon', '-d'], true)) {
            $this->config['swoole']['daemonize'] = 1;
        }

        //创建启动服务
        $http->create($this->config);
        $this->registerPort = $http->getRegisterPort();
        EventManager::get()->trigger('tcp.run.before', $this, $http);
        $server = $http->getServer();
        $server->on('start', [$this, 'onStart']);
        $server->on('request', [$this, 'onRequest']);
        $server->on('shutdown', [$this, 'onShutdown']);
        $server->start();
        EventManager::get()->trigger('tcp.run.after', $this, $http);
    }

    /**
     * 服务启动回调
     * @throws \Exception
     */
    public function onStart()
    {
        //设置代理
        $this->io->success('tcp server is running');
        $ip = swoole_get_local_ip();
        $listen = $this->config['server']['listen'] == '0.0.0.0' ? array_shift($ip) : $this->config['server']['listen'];
        $this->io->text('<info>open:</info> ' . $listen . ':' . $this->config['server']['port']);
        go(function () {
            //启动定时器
            TimerAlias::getInstance()->createTimer();
        });
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \Exception
     */
    public function onRequest(Request $request, Response $response)
    {
        //注册服务
        if ($request->server['server_port'] == $this->registerPort) {
           $this->apiDispatch($request, $response);
        } else {
            $this->dispatch($request, $response);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return array|bool
     */
    protected function apiDispatch($request, $response){
        $response->header('Content-Type', 'application/json');
        try {
            $out = ApiRouter::getInstance()->dispatch($request);
        } catch (\Exception $e) {
            return Out::error($response, $e->getMessage());
        }
        if ($out === null) {
            return Out::notFound($response);
        }
        Out::success($response, $out);
        return true;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    protected function dispatch($request, $response){
        $path = $request->server['path_info'];
        if ($path == '/favicon.ico') {
            return false;
        }
        $response->header('Content-Type', 'application/json');
        EventManager::get()->trigger('http.request', null, ['request' => &$request]);
        try {
            $proxy = Proxy::getInstance();
            $out = $proxy->dispatch($request);
        } catch (\Exception $e) {
            $response->status(500);
            $response->write($e->getMessage());
            return false;
        }
        if (!$out) {
            $response->status(500);
            $response->write('error');
            return false;
        }
        $response->write($out);
        go(function () use ($request, $out) {
            EventManager::get()->trigger('tcp.response.after', $this, [
                'request' => $request,
                'out' => $out,
            ]);
        });
    }

    /**
     * 服务结束
     */
    public function onShutdown()
    {
        EventManager::get()->trigger('tcp.shutDown', $this);
    }

}

