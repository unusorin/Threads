<?php
/**
 * src/unusorin/Threads/Thread.php
 * @author Sorin Badea <sorin.badea91@gmail.com>
 */
namespace unusorin\Threads;

/**
 * Class Thread
 * @package unusorin\Threads
 */
abstract class Thread
{
    public static $sleepInterval = 1;
    public static $autoPause = true;
    protected $paused = false;

    public function run()
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('Could not start thread');
        } else {
            if ($pid) {
                exit;
            } else {
                $this->registerSignals();
                while (true) {
                    $this->sleepWhilePaused();
                    $this->toRun();
                    if (static::$autoPause) {
                        $this->paused = true;
                    }
                }
            }
        }
    }

    protected function registerSignals()
    {
        declare(ticks = 1);
        $context = $this;
        $handler = function ($signal) use ($context) {
            $context->handleSignal($signal);
        };
        pcntl_signal(
            ThreadSignals::STOP,
            $handler
        );
        pcntl_signal(
            ThreadSignals::RESUME,
            $handler
        );
    }

    public function handleSignal($signal)
    {
        switch ($signal) {
            case ThreadSignals::STOP:
                $this->paused = true;
                break;
            case ThreadSignals::RESUME:
                $this->paused = false;
                break;
        }
    }

    protected function sleepWhilePaused()
    {
        while ($this->paused) {
            sleep(static::$sleepInterval);
        }
    }

    abstract public function toRun();
}