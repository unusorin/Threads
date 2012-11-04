<?php
include_once "memoryblock.class.php";
class Thread{

    const SignalKill = 1;
    const SignalStop = 2;
    const SignalContinue = 3;
    const SignalCommunicate = 4;

    public $User = NULL;
    public $ThreadPid = NULL;
    public $Time = NULL;

    public static $Paused = FALSE;
    public static $AutoPause = TRUE;
    public static $Data = array();

    public function __construct($PID=NULL,$User=NULL,$Time=NULL){

        $this->ThreadPid=$PID;
        $this->User=$User;
        $this->Time=$Time;

    }

    /**
     * send a signal to a thread
     * @param Thread $Signal
     */
    public function SendSignal($Signal){
        switch($Signal){
            case Thread::SignalKill:
                $this->KillThread();
                break;
            case Thread::SignalContinue:
                exec("kill -12 ".$this->ThreadPid);
                break;
            case Thread::SignalStop:
                exec("kill -10 ".$this->ThreadPid);
                break;
        }
    }

    /**
     * Pause current thread
     */
    public function Pause()
    {
        while (self::$Paused) {
            pcntl_signal_dispatch();
            sleep(1);
        }
    }

    /**
    * kill a thread
    * @return string
    */
    private function KillThread(){
        return shell_exec("kill -9 " . $this->ThreadPid);
    }

    /**
     * send data to a thread
     * @param array $Data
     */
    public function SendData($Data){
        $MemoryBlock=MemoryBlock::Get($this->ThreadPid);
        $MemoryBlock->Data(serialize($Data));
    }
    /**
    * register signals to be caught
    */
    public static function RegisterSignals(){
        $ClassName = get_called_class();
        pcntl_signal(SIGUSR1,get_called_class()."::HandleSignal");
        pcntl_signal(SIGUSR2,get_called_class()."::HandleSignal");
    }

    /**
     * receive data from thread controller
     */
    public static function ReceiveData(){
        $MemoryBlock= MemoryBlock::Get(getmypid());
        self::$Data=@unserialize($MemoryBlock);
    }
    /**
    * Handle a specific signal
    * @param int $Signal
    */
    public static function HandleSignal($Signal){
        switch($Signal){
            case SIGUSR1:
                self::$Paused=TRUE;
                break;
            case SIGUSR2:
                self::$Paused=FALSE;
                break;
        }
    }

    /**
     * start the current thread
     */
    public function RunThread(){
        self::RegisterSignals();
        while(TRUE){
            //resolve pending signals
            pcntl_signal_dispatch();

            if(self::$Paused){
                $this->Pause();
            }
            self::ReceiveData();
            $this->ToRun();
            if(self::$AutoPause){
                self::$Paused=TRUE;
            }
        }
    }

    /**
     * start a php script asynchronous
     * @param $ScriptPath
     */
    public static function Start($ScriptPath){
        pclose(popen("php  " . $ScriptPath . " -ThreadId=".sha1(microtime())." &> /dev/null &", "r"));
    }

    /**
     * list all threads
     * @return array Thread
     */
    public static function ListAll(){

        $job  = shell_exec("ps aux | grep \"-ThreadId\"");

        $rows = explode("\n", $job);
        $jobs = array();
        foreach ($rows as $row) {
            if (strstr($row, "-ThreadId=")) {
                $parts  = explode(" ", $row);
                $index  = -1;
                $_parts = array();
                foreach ($parts as $key=> $part) {
                    if (trim($part) == "") {
                        unset( $parts[$key] );
                    } else {
                        $_parts[++$index] = $part;
                    }
                }
                $jobs[] = new Thread($_parts[1],$_parts[0],$_parts[8]);
            }

        }
        return $jobs;
    }
    /**
     * code to be executed in the loop
     */
    public function ToRun(){

    }

}
