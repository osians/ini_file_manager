<?php

class FileWriter
{
    public static function rewrite($fileName, $content)
    {
        $fp = fopen($fileName, 'w');
        
        if (!$fp) {
            throw new Exception('Cant open file for write');
        }

        $startTime = microtime(TRUE);

        do {
            $canWrite = flock($fp, LOCK_EX);
            if (!$canWrite) {
                usleep(round(rand(0, 100)*1000));
            }
        } while ((!$canWrite) && ((microtime(TRUE)-$startTime) < 5));

        if ($canWrite) {            
            fwrite($fp, $content);
            flock($fp, LOCK_UN);
        }

        fclose($fp);
    }
}
