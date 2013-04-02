<?php
include_once "memoryblock.class.php";
class Thread
{

    const signalKill        = 1;
    const signalStop        = 2;
    const signalContinue    = 3;
    const signalCommunicate = 4;
    public static $paused = false;
    public static $autoPause = true;
    public static $data = array();
    public $user = null;
    public $threadPid = null;
    public $time = null;

    public function __construct($PID = null, $user = null, $time = null)
    {

        $this->threadPid = $PID;
        $this->user      = $user;
        $this->time      = $time;

    }

    /**
     * Handle a specific signal
     *
     * @param int $signal
     */
    public static function handleSignal($signal)
    {
        switch ($signal) {
            case SIGUSR1:
                self::$paused = true;
                break;
            case SIGUSR2:
                self::$paused = false;
                break;
        }
    }

    /**
     * start a php script asynchronous
     *
     * @param $scriptPath
     */
    public static function start($scriptPath)
    {
        pclose(popen("php  " . $scriptPath . " -ThreadId=" . sha1(microtime()) . " &> /dev/null &", "r"));
    }

    /**
     * list all Threads
     *
     * @return array Thread
     */
    public static function listAll()
    {

        $job = shell_exec("ps aux | grep \"-ThreadId\"");

        $rows = explode("\n", $job);
        $jobs = array();
        foreach ($rows as $row) {
            if (strstr($row, "-ThreadId=")) {
                $parts  = explode(" ", $row);
                $index  = -1;
                $_parts = array();
                foreach ($parts as $key => $part) {
                    if (trim($part) == "") {
                        unset($parts[$key]);
                    } else {
                        $_parts[++$index] = $part;
                    }
                }
                $jobs[] = new Thread($_parts[1], $_parts[0], $_parts[8]);
            }

        }
        return $jobs;
    }

    /**
     * fork current process and execute the code asynchronously
     *
     * @param       $callback callback to be executed in the child process
     * @param bool  $wait     flag if the parent wait for the child (default false)
     * @param array $params   params to be passed to the callback function
     */
    public static function fork($callback, $wait = false, $params = array())
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            return;
        } else {
            if ($pid) {
                if ($wait) {
                    pcntl_wait($status);
                }
            } else {
                call_user_func_array($callback, $params);
                exit;
            }
        }
    }

    /**
     * send a signal to a thread
     *
     * @param Thread $signal
     */
    public function sendSignal($signal)
    {
        switch ($signal) {
            case Thread::signalKill:
                $this->killThread();
                break;
            case Thread::signalContinue:
                exec("kill -12 " . $this->threadPid);
                break;
            case Thread::signalStop:
                exec("kill -10 " . $this->threadPid);
                break;
        }
    }

    /**
     * kill a thread
     *
     * @return string
     */
    private function killThread()
    {
        return shell_exec("kill -9 " . $this->threadPid);
    }

    /**
     * send data to a thread
     *
     * @param array $data
     */
    public function sendData($data)
    {
        $memoryBlock = MemoryBlock::get($this->threadPid);
        $memoryBlock->data(serialize($data));
    }

    /**
     * start the current thread
     */
    public function runThread()
    {
        self::registerSignals();
        while (true) {
            //resolve pending signals
            pcntl_signal_dispatch();

            if (self::$paused) {
                $this->pause();
            }
            self::receiveData();
            $this->toRun();
            if (self::$autoPause) {
                self::$paused = true;
            }
        }
    }

    /**
     * register signals to be caught
     */
    public static function registerSignals()
    {
        pcntl_signal(SIGUSR1, get_called_class() . "::HandleSignal");
        pcntl_signal(SIGUSR2, get_called_class() . "::HandleSignal");
    }

    /**
     * Pause current thread
     */
    public function pause()
    {
        while (self::$paused) {
            pcntl_signal_dispatch();
            sleep(1);
        }
    }

    /**
     * receive data from thread controller
     */
    public static function receiveData()
    {
        $memoryBlock = MemoryBlock::get(getmypid());
        self::$data  = @unserialize($memoryBlock);
    }

    /**
     * code to be executed in the loop
     */
    public function toRun()
    {

    }

}
