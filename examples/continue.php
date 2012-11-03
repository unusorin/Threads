<?php
include_once "../thread.class.php";
$thread = new Thread(9673);

$thread->SendData(array("today"=>time()));
$thread->SendSignal(Thread::SignalContinue);