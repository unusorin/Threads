<?php
include_once "../thread.class.php";
/**
 * for testing purposes run this with php dummythread.php
 */
class DummyThread extends Thread{
    public function ToRun(){
        echo "Current data:".print_r(self::$Data,true);
        echo "[Pid:".getmypid()."][".time()."]Still alive ... \n";
        sleep(1);
    }
}

$thread = new DummyThread();
$thread->RunThread();