<?php
include_once "../thread.class.php";
/**
 * for testing purposes run this with php dummythread.php
 */
class DummyThread extends Thread
{
    public function toRun()
    {
        echo "Current data:" . print_r(self::$data, TRUE);
        echo "[Pid:" . getmypid() . "][" . time() . "]Still alive ... \n";
        sleep(1);
    }
}

$thread = new DummyThread();
$thread->runThread();