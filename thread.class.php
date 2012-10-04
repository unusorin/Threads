<?php
class Thread{
    public $User = NULL;
    public $ThreadPid = NULL;
    public $Time = NULL;
    public static $Paused = false;

    public function __construct($PID=NULL,$User=NULL,$Time=NULL){

        $this->ThreadPid=$PID;
        $this->User=$User;
        $this->Time=$Time;

    }

    /**
     * send a signal to a thread
     * @param ThreadManager $Signal
     */
    public function SendSignal($Signal){
        switch($Signal){
            case ThreadManager::SignalKill:
                $this->KillThread();
                break;
            case ThreadManager::SignalContinue:
                exec("kill -s 10 ".$this->ThreadPid);
                break;
            case ThreadManager::SignalStop:
                exec("kill -s SIGUSR2 ".$this->ThreadPid);
                break;
            case ThreadManager::SignalCommunicate:
                exec("kill -s 29 ".$this->ThreadPid);
                break;
        }
    }

    /**
     * Pause current thread
     */
    public function Pause()
    {
        while (self::$Paused) {
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
    * register signals to be caught
    */
    public static function RegisterSignals(){
        pcntl_signal(SIGUSR1, "Thread::HandleSignal");
        pcntl_signal(SIGUSR2, "Thread::HandleSignal");
        pcntl_signal(29, "Thread::HandleSignal");
    }

    /**
    * Handle a specific signal
    * @param int $Signal
    */
    public static function HandleSignal($Signal){
        switch($Signal){
            case SIGUSR1:
                self::$Paused=true;
                break;
            case SIGUSR2:
                self::$Paused=false;
                break;
            case 29;
                self::$Paused=true;
                //TODO: comunicate with controller
                break;
        }
    }

    /**
     * start the current thread
     */
    public function RunThread(){
        self::RegisterSignals();
        while(true){
            if(self::$Paused){
                $this->Pause();
            }
            $this->ToRun();
        }
    }

    /**
     * code to be executed in the loop
     */
    public function ToRun(){

    }

}
