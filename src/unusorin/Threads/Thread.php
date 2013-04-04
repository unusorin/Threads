<?php
/**
 * src/unusorin/Threads/Thread.php
 * @author Sorin Badea <sorin.badea91@gmail.com>
 */
namespace unusorin\Threads;

use unusorin\Threads\Exceptions\ThreadException;

/**
 * Class Thread
 * @package unusorin\Threads
 */
abstract class Thread
{
    /**
     * sleep interval between signal checks
     * @var int
     */
    public static $sleepInterval = 1;
    /**
     * flag to auto pause after each execution of the code from toRun method
     * @var bool
     */
    public static $autoPause = true;
    /**
     * flag to execute in loop the code from toRun method
     * @var bool
     */
    public static $loop = true;

    /**
     * paused flag
     * @var bool
     */
    protected $paused = false;
    /**
     * thread identifier
     * @var string
     */
    protected $identifier = null;
    /**
     * @var null|ThreadPackage
     */
    protected $package = null;

    /**
     * thread constructor
     * @param null|string $identifier
     */
    public function __construct($identifier = null)
    {
        $this->identifier = is_null($identifier) ? uniqid("thread_", true) : $identifier;
        $this->package = new ThreadPackage(getmypid());
    }

    /**
     * start thread
     * @param bool $wait
     * @return int
     * @throws Exceptions\ThreadException
     */
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

    /**
     * register process signals
     */
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

    /**
     * handle  received signal
     * @param int $signal
     */
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

    /**
     * sleep while thread is paused
     */
    protected function sleepWhilePaused()
    {
        while ($this->paused) {
            sleep(static::$sleepInterval);
        }
    }

    /**
     * save thread pid in pid file
     */
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
        $this->package->pid = getmypid();
        $this->savePackage();
    }

    /**
     * code that will be executed in the thread
     * @return mixed
     */
    abstract public function toRun();

    /**
     * check if received message
     * @return bool
     */
    protected function receivedMessage()
    {
        $this->getPackage();
        if (!is_null($this->package->clientMessage)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get received message
     * @return mixed
     */
    protected function receiveMessage()
    {
        if ($this->receivedMessage()) {
            $message = $this->package->clientMessage;
            $this->package->clientMessage = null;
            $this->package->threadRead = true;
            $this->savePackage();
            return $message;
        } else {
            return null;
        }
    }

    /**
     * send message to first ThreadController attached
     * @param string $message
     */
    protected function sendMessage($message)
    {
        $this->getPackage();
        $this->package->threadMessage = $message;
        $this->package->clientRead = false;
        $this->savePackage();
    }

    /**
     * get pid file content
     * @return ThreadPackage
     * @throws Exceptions\ThreadException
     */
    protected function getPackage()
    {
        $dir = defined('THREADS_DIR') ? THREADS_DIR : 'threads';
        if ($package = json_decode(file_get_contents($dir . '/' . $this->identifier . '.pid'))) {
            $this->package = $package;
            return $package;
        } else {
            throw new ThreadException('Cannot find thread file', ThreadException::NOT_FOUND_THREAD_FILE);
        }
    }

    /**
     * save thread package in the pid file
     */
    protected function savePackage()
    {
        $dir = defined('THREADS_DIR') ? THREADS_DIR : 'threads';
        file_put_contents($dir . '/' . $this->identifier . '.pid', json_encode($this->package));
    }
}
