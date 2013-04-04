<?php
/**
 * src/unusorin/Threads/ThreadsManager.php
 * @author Sorin Badea <sorin.badea91@gmail.com>
 */
namespace unusorin\Threads;

/**
 * Class Thread
 * @package unusorin\Threads
 */

class ThreadsManager
{
    /**
     * get active threads
     * @return array
     */
    public function getRunningThreads()
    {
        $dir = $this->getThreadsPidDir();
        if (is_dir($dir)) {
            $threads = array_filter(
                scandir($dir),
                function ($thread) {
                    if ($thread == '.' || $thread == '..') {
                        return false;
                    } else {
                        return true;
                    }
                });
        } else {
            $threads = array();
        }
        $_threads = array();
        array_map(
            function ($thread) use ($dir, &$_threads) {
                if ($package = json_decode(file_get_contents($dir . "/" . $thread))) {
                    $pid = $package->pid;
                    if (posix_kill($pid, 0)) {
                        $thread = substr($thread, 0, strlen($thread) - 4);
                        $_threads[$thread] = $pid;
                    }
                }

            },
            $threads
        );
        $this->cleanUpPIDs($_threads);
        return $_threads;
    }

    /**
     * delete pid files that point to inactive threads
     * @param array $running
     */
    private function cleanUpPIDs(array $running)
    {
        $dir = $this->getThreadsPidDir();
        $pids = scandir($dir);
        array_map(
            function ($fileName) use ($running, $dir) {
                if ($fileName != '.' && $fileName != '..') {
                    if (!isset($running[substr($fileName, 0, strlen($fileName) - 4)])) {
                        unlink($dir . '/' . $fileName);
                    }
                }
            },
            $pids
        );
    }

    /**
     * get thread by name
     * @param $name
     * @return ThreadController|null
     */
    public function getThreadByName($name)
    {
        $threads = $this->getRunningThreads();
        if (isset($threads[$name])) {
            return new ThreadController($threads[$name], $name);
        }
    }

    /**
     * get dir that containt thread's pid files
     * @return string
     */
    public function getThreadsPidDir()
    {
        return defined('THREADS_DIR') ? THREADS_DIR : 'threads';
    }
}
