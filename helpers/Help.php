<?php

namespace app\helpers;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Facebook;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use Yii;
use yii\helpers\Url;

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
            return file_put_contents(Yii::getAlias('@runtime/logs/'.$filename), $log, FILE_APPEND);
        }catch (\Exception $ex){
            return false;
        }
    }

    /**
     * For friendly url's and transliterations
     * @param $str
     * @param array $options
     * @return string
     * @author Sean Murphy <sean@iamseanmurphy.com>
     * @copyright Copyright 2012 Sean Murphy. All rights reserved.
     * @license http://creativecommons.org/publicdomain/zero/1.0/
     */
    public static function slug($str, $options = array()) {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => true,
        );

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',

            // Latin symbols
            '©' => '(c)',

            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',

            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',

            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z',

            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',

            // Latvian - Lithuanian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z', 'Ė' => 'E', 'Į' => 'I', 'Ų' => 'U',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z', 'ė' => 'e', 'į' => 'i', 'ų' => 'u',
        );

        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }

    /**
     * Generates random string
     * @param int $length
     * @param bool|false $numbersOnly
     * @return string
     */
    public static function rds($length = 10,$numbersOnly = false) {

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

    /**
     * If site redirects to some url - this will return this url
     * @param $url
     * @return null|string
     */
    public static function redirurl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0");
        curl_exec($ch);

        $response = curl_exec($ch);
        preg_match_all('/^Location:(.*)$/mi', $response, $matches);
        curl_close($ch);

        return !empty($matches[1]) ? trim($matches[1][0]) : null;
    }

    /**
     * Returns data from db and caches query (if needed)
     * @param callable $callback
     * @param bool $cache
     * @return mixed
     */
    public static function cquery(callable $callback, $cache = true)
    {
        if(!$cache){
            return call_user_func($callback,Yii::$app->db);
        }
        return Yii::$app->db->cache($callback);
    }

    /**
     * Swaps elements in array
     * @param $array
     * @param $index1
     * @param $index2
     * @return bool
     */
    public static function swap(&$array,$index1,$index2)
    {
        if(empty($array[$index1]) || empty($array[$index2])){
            return false;
        }

        $tmp = $array[$index1];
        $array[$index1] = $array[$index2];
        $array[$index2] = $tmp;

        return true;
    }

    /**
     * Makes request to facebook API fot posting comment
     * @param $objectId
     * @param $comment
     * @return null|string
     */
    public static function fbcomment($objectId, $comment)
    {
        try{
            $fb = new Facebook([
                'app_id' => Yii::$app->params['facebook']['app_id'],
                'app_secret' => Yii::$app->params['facebook']['app_secret'],
                'default_access_token' => Yii::$app->params['facebook']['bot_token'],
            ]);

            $result = $fb->post('/'.$objectId.'/comments',['message' => $comment]);
            $body = $result->getDecodedBody();

        }catch (FacebookResponseException $ex){
            return null;
        }

        return !empty($body['id']) ? $body['id'] : null;
    }

    /**
     * Returns canonical URL to current page
     * @param bool $abs
     * @param bool $https
     * @return string
     */
    public static function canonical($abs = true, $https = false)
    {
        $controllerId = Yii::$app->controller->id;
        $actionId = Yii::$app->controller->action->id;
        $id = Yii::$app->request->get('id',null);
        $type = Yii::$app->request->get('type',null);
        $title = Yii::$app->request->get('title',null);

        return Url::to(["/$controllerId/$actionId", 'id' => $id, 'type' => $type, 'title' => $title],true);
    }

    /**
     * Converts youtube URL to embed url
     * @param $string
     * @return mixed
     */
    public static function youtubeurl($string) {
        $urlComponents = parse_url($string);
        $queryString = $urlComponents['query'];
        $params = [];
        parse_str($queryString,$params);

        if(!empty($params['v'])){
            $v = $params['v'];
            return "https://www.youtube.com/embed/$v";
        }

        return $string;
    }

    /**
     * Converts youtube URL to video ID
     * @param $string
     * @return mixed
     */
    public static function youtubeid($string){
        $urlComponents = parse_url($string);
        $queryString = $urlComponents['query'];

        $params = [];
        parse_str($queryString,$params);

        return ArrayHelper::getValue($params,'v');
    }

    /**
     * Executes some code just for developer's IPS
     * @param callable $callback
     * @return bool|mixed
     */
    public static function devexe(callable $callback){

        $ips = [
            '78.56.14.109',
            '78.31.184.83'
        ];

        if(in_array($_SERVER['REMOTE_ADDR'],$ips)){
            return call_user_func($callback);
        }

        return false;
    }

    /**
     * Formats datetime string
     * @param $datetime
     * @param string $format
     * @return bool|string
     */
    public static function datefmt($datetime,$format = '%e %B %Y, %R',$strftime = true)
    {
        $dt = new \DateTime($datetime);

        if($strftime){
            return self::rudate($format,$dt->getTimestamp());
        }

        return date($format,$dt->getTimestamp());
    }

    /**
     * Format russian date
     * @param $format
     * @param bool|false $date
     * @return string
     */
    public static function rudate($format, $date = false) {
        setlocale(LC_ALL, 'ru_RU.UTF-8');

        if ($date === false) {
            $date = time();
        }

        if ($format === '') {
            $format = '%e&nbsp;%bg&nbsp;%Y&nbsp;г.';
        }

        $months = explode("|", '|января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря');
        $format = preg_replace("~\%B~", $months[date('n', $date)], $format);
        return strftime($format, $date);
    }
}