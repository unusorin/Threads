<?php
class ThreadManager
{
    const SignalKill = 1;
    const SignalStop = 2;
    const SignalContinue = 3;
    const SignalCommunicate = 4;

    public static $Interpreter = "php";

    public  static function RunScript($ScriptPath)
    {
        pclose(popen(self::$Interpreter . " " . $ScriptPath . " -ThreadId=".sha1(microtime())." /dev/null &", "r"));
    }

    /**
     * @return Thread[]
     */
    public static function GetThreads(){

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
}