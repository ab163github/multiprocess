<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) kcloze <pei.greet@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kcloze\MultiProcess;

class Console
{
    public $logger    = null;
    private $config   = [];

    public function __construct($config)
    {
        Config::setConfig($config);
        $this->config = Config::getConfig();
        $this->logger = Logs::getLogger(Config::getConfig()['logPath'] ?? []);
    }

    public function run()
    {
        $this->getOpt();
    }

    public function start()
    {
        //启动
        $process = new Process();
        $process->start();
    }

    /**
     * 给主进程发送信号：
     *  SIGUSR1 自定义信号，让子进程平滑退出
     *  SIGTERM 程序终止，让子进程强制退出.
     *
     * @param [type] $signal
     */
    public function stop($signal=SIGUSR1)
    {
        $this->logger->log(($signal == SIGUSR1) ? 'smooth to exit...' : 'force to exit...');

        if (isset($this->config['pidPath']) && !empty($this->config['pidPath'])) {
            $masterPidFile=$this->config['pidPath'] . '/master.pid';
        } else {
            $masterPidFile=APP_PATH . '/log/master.pid';
        }

        if (file_exists($masterPidFile)) {
            $ppid=file_get_contents($masterPidFile);
            if (empty($ppid)) {
                exit('service is not running' . PHP_EOL);
            }
            if (function_exists('posix_kill')) {
                //macOS 只接受SIGUSR1信号
                //$signal=(PHP_OS == 'Darwin') ? SIGKILL : $signal;
                if (@\Swoole\Process::kill($ppid, $signal)) {
                    $this->logger->log('[pid: ' . $ppid . '] has been stopped success');
                } else {
                    $this->logger->log('[pid: ' . $ppid . '] has been stopped fail');
                }
            } else {
                system('kill -' . $signal . $ppid);
                $this->logger->log('[pid: ' . $ppid . '] has been stopped success');
            }
        } else {
            exit('service is not running' . PHP_EOL);
        }
    }

    public function restart()
    {
        $this->logger->log('restarting...');
        $this->exit();
        sleep(3);
        $this->start();
    }

    public function exit()
    {
        $this->stop(SIGTERM);
    }

    public function getOpt()
    {
        global $argv;
        if (empty($argv[1])) {
            $this->printHelpMessage();
            exit(1);
        }
        $opt=$argv[1];
        switch ($opt) {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'exit':
                $this->exit();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'help':
                $this->printHelpMessage();
                break;

            default:
                $this->printHelpMessage();
                break;
        }
    }

    public function printHelpMessage()
    {
        $msg=<<<'EOF'
NAME
      php multiprocess - manage multiprocess

SYNOPSIS
      php multiprocess command [options]
          Manage multiprocess daemons.


WORKFLOWS


      help [command]
      Show this help, or workflow help for command.


      restart
      Stop, then start multiprocess master and workers.

      start
      Start multiprocess master and workers.

      stop
      Wait all running workers smooth exit, please check multiprocess status for a while.

      exit
      Kill all running workers and master PIDs.


EOF;
        echo $msg;
    }
}
