<?php

namespace app\helpers;

class Help
{
    /**
     * Debug variables
     * @param $var
     * @param bool|false $return
     * @return string
     */
    public static function debug($var, $return = false)
    {
        $result = "";
        //debug
        ob_start();
        print_r($var);
        $out = ob_get_clean();

        if(!$return){
            echo "<pre>";
            echo htmlentities($out);
            echo "</pre>";
        }else{
            $result = "<pre>".htmlentities($out)."</pre>";
        }

        return $result;
    }

    /**
     * Log to file
     * @param $filename
     * @param $text
     * @return bool|int
     */
    public static function log($filename,$text)
    {
        $log = date('Y-m-d H:i:s',time()).' - '.$text."\n";

        try{
            return file_put_contents($filename, $log, FILE_APPEND);
        }catch (\Exception $ex){
            return false;
        }
    }
}