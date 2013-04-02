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

use unusorin\Threads\Exceptions\ThreadException;

abstract class Thread
{
    public static $sleepInterval = 1;
    public static $autoPause = true;
    public static $loop = true;
    protected $paused = false;
    protected $identifier = null;

    public function __construct($identifier = null)
    {
        $this->identifier = is_null($identifier) ? uniqid("thread_", true) : $identifier;
    }

    public function run($wait = false)
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new ThreadException("Could not fork", ThreadException::COULD_NOT_FORK);
        } else {
            if ($pid) {
                if ($wait) {
                    pcntl_wait($status);
                    return $status;
                }
                return $pid;
            } else {
                $this->savePID();
                $this->registerSignals();
                while (true) {
                    $this->sleepWhilePaused();
                    $this->toRun();
                    if (!static::$loop) {
                        exit();
                    }
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

    protected function savePID()
    {
        if (defined('THREADS_DIR')) {
            $dir = THREADS_DIR;
        } else {
            $dir = 'threads';
        }
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents($dir . "/" . $this->identifier . ".pid", getmypid());
    }

    abstract public function toRun();
}