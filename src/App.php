<?php

namespace suframe\proxy;

use suframe\core\components\Config;
use suframe\core\traits\Singleton;
use suframe\proxy\driver\HttpDriver;
use suframe\proxy\driver\TcpDriver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class App
{
    use Singleton;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $dispatchType = Config::getInstance()->get('tcp.dispatchType', 'http');
        switch ($dispatchType){
            case 'http':
                HttpDriver::getInstance()->run($input, $output);
                break;
            default:
                TcpDriver::getInstance()->run($input, $output);
        }
    }

}