<?php

namespace app\helpers;

class Help
{
    /**
     * Дебаг различных переменных
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
     * Логирование в файл
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

    /**
     * Генерация случайной строки заданной длины
     * @param int $length
     * @param bool|false $numbersOnly
     * @return string
     */
    public static function rndstr($length = 10,$numbersOnly = false) {

        $charactersNr = '0123456789';
        $charactersChar = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = $numbersOnly ? $charactersNr : $charactersNr.$charactersChar;

        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}