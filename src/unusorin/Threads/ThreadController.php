<?php
/**
 * src/unusorin/Threads/ThreadController.php
 * @author <sorin.badea91@gmail.com>
 */
namespace unusorin\Threads;

use unusorin\Threads\Exceptions\ThreadException;

/**
 * Class ThreadController
 * @package unusorin\Threads
 */
class ThreadController
{
    /**
     * thread pid
     * @var int
     */
    protected $pid = 0;
    /**
     * thread name
     * @var int
     */
    protected $name = 0;
    /**
     * @var ThreadPackage
     */
    protected $package = null;

    /**
     * class constructor
     * @param int $pid
     * @param string $name
     */
    public function __construct($pid, $name)
    {
        $this->pid = $pid;
        $this->name = $name;
        $this->package = new ThreadPackage($this->pid);
    }

    /**
     * pause thread
     * @return bool
     */
    public function pause()
    {
        return posix_kill($this->pid, ThreadSignals::STOP);
    }

    /**
     * resume thread
     * @return bool
     */
    public function resume()
    {
        return posix_kill($this->pid, ThreadSignals::RESUME);
    }

    /**
     * kill thread
     * @return bool
     */
    public function kill()
    {
        return posix_kill($this->pid, ThreadSignals::KILL);
    }

    /**
     * check if thread is alive
     * @return bool
     */
    public function isAlive()
    {
        return posix_kill($this->pid, 0);
    }

    /**
     * check if received any message
     * @return bool
     */
    public function receivedMessage()
    {
        $this->getPackage();
        if (!is_null($this->package->threadMessage)) {
            return true;
        }
        return false;
    }

    /**
     * get received message
     * @return mixed
     */
    public function receiveMessage()
    {
        if ($this->receivedMessage()) {
            $message = $this->package->threadMessage;
            $this->package->clientRead = true;
            $this->package->threadMessage = null;
            $this->savePackage();
            return $message;
        } else {
            return null;
        }
    }

    /**
     * send message to thread
     * @param string $message
     */
    public function sendMessage($message)
    {
        $this->getPackage();
        $this->package->clientMessage = $message;
        $this->package->threadRead = false;
        $this->savePackage();
    }

    /**
     * get pid file content
     * @return null|ThreadPackage
     * @throws Exceptions\ThreadException
     */
    protected function getPackage()
    {
        if (!is_null($this->package)) {
            return $this->package;
        }
        $dir = defined('THREADS_DIR') ? THREADS_DIR : 'threads';
        if ($package = json_decode(file_get_contents($dir . '/' . $this->name . '.pid'))) {
            $this->package = $package;
            return $package;
        } else {
            throw new ThreadException('Cannot find thread file', ThreadException::NOT_FOUND_THREAD_FILE);
        }
    }

    /**
     * save thread package in pid file
     */
    protected function savePackage()
    {
        $dir = defined('THREADS_DIR') ? THREADS_DIR : 'threads';
        file_put_contents($dir . '/' . $this->name . '.pid', json_encode($this->package));
    }
}
