<?php
namespace suframe\proxy\driver;

use suframe\core\components\Config;
use suframe\core\components\console\SymfonyStyle;
use suframe\core\components\event\EventManager;
use suframe\core\components\net\http\Server;
use suframe\core\components\net\tcp\Proxy;
use suframe\core\components\proxy\Client;
use suframe\core\traits\Singleton;
use Swoole\Http\Response;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Http\Request;

class TcpDriver {
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $config = Config::getInstance();
        $tcp = new Server();
        $this->config = $config->get('tcp')->toArray();
        //设置代理
        $this->initProxy();
        //守护进程运行
        if (true === $input->hasParameterOption(['--daemon', '-d'], true)) {
            $this->config['swoole']['daemonize'] = 1;
        }

        //创建启动服务
        $tcp->create($this->config);
        EventManager::get()->trigger('tcp.run.before', $this, $tcp);
        $server = $tcp->getServer();
        $server->on('receive', [$this, 'onReceive']);
        $server->on('start', [$this, 'onStart']);
        $server->on('shutdown', [$this, 'onShutdown']);
        $server->start();
        EventManager::get()->trigger('tcp.run.after', $this, $tcp);
    }

    /**
     * 服务启动回调
     * @throws \Exception
     */
    public function onStart()
    {
        $this->io->success('tcp server is running');
        $ip = swoole_get_local_ip();
        $listen = $this->config['server']['listen'] == '0.0.0.0' ? array_shift($ip) : $this->config['server']['listen'];
        $this->io->text('<info>open:</info> ' . $listen . ':' . $this->config['server']['port']);
        //注册服务
        go(function () {
            Client::getInstance()->register();
        });
    }

    /**
     * tcp请求回调
     * @param \Swoole\Server $server
     * @param $fd
     * @param $reactor_id
     * @param $data
     */
    public function onReceive(\Swoole\Server $server, $fd, $reactor_id, $data)
    {
        EventManager::get()->trigger('tcp.request', $this, ['data' => &$data]);
        $out = $this->proxy->dispatch($data);
        $server->send($fd, $out);
        $server->close($fd);
        go(function () use ($data, $out) {
            EventManager::get()->trigger('tcp.response.after', $this, [
                'request' => $data,
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

    /**
     * @throws \Exception
     */
    protected function initProxy()
    {
        $config = [
            [
                'path' => '/news',
                'host' => '127.0.0.1',
                'port' => 9502,
            ],
            [
                'path' => '/goods',
                'host' => '127.0.0.1',
                'port' => 9602,
            ],
        ];
        $this->proxy = new Proxy($config);

    }

    /**
     * @return Proxy
     */
    public function getProxy(): Proxy
    {
        return $this->proxy;
    }
}

