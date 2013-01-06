<?php
include_once "memoryblock.class.php";
class Thread
{

    const signalKill        = 1;
    const signalStop        = 2;
    const signalContinue    = 3;
    const signalCommunicate = 4;

    public $user = NULL;
    public $threadPid = NULL;
    public $time = NULL;

    public static $paused = FALSE;
    public static $autoPause = TRUE;
    public static $data = array();

    public function __construct($PID = NULL, $user = NULL, $time = NULL)
    {

        $this->threadPid = $PID;
        $this->user      = $user;
        $this->time      = $time;

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
     * register signals to be caught
     */
    public static function registerSignals()
    {
        pcntl_signal(SIGUSR1, get_called_class() . "::HandleSignal");
        pcntl_signal(SIGUSR2, get_called_class() . "::HandleSignal");
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
     * Handle a specific signal
     *
     * @param int $signal
     */
    public static function handleSignal($signal)
    {
        switch ($signal) {
            case SIGUSR1:
                self::$paused = TRUE;
                break;
            case SIGUSR2:
                self::$paused = FALSE;
                break;
        }
    }

    /**
     * start the current thread
     */
    public function runThread()
    {
        self::registerSignals();
        while (TRUE) {
            //resolve pending signals
            pcntl_signal_dispatch();

            if (self::$paused) {
                $this->pause();
            }
            self::receiveData();
            $this->toRun();
            if (self::$autoPause) {
                self::$paused = TRUE;
            }
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
     * list all threads
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
    public static function fork($callback, $wait = FALSE, $params = array())
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
     * code to be executed in the loop
     */
    public function toRun()
    {

    }

}
