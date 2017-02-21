<?php

namespace app\helpers;

use linslin\yii2\curl\Curl;
use Yii;
use app\models\User;
use yii\helpers\ArrayHelper;

class AdminizatorApi
{
    const API_BASE_URL = "https://adminizator.com/api/";
    const API_CURL_TIMEOUT = 15;

    /**
     * @var self
     */
    private static $_instance = null;

    /**
     * @var \app\models\User|null
     */
    public $basicAdmin = null;

    /**
     * @var string|null
     */
    public $apiSyncKey = null;

    /**
     * @var string|null
     */
    public $apiSyncHash = null;

    /**
     * @var string|null
     */
    public $fbClientId = null;

    /**
     * @return AdminizatorApi
     */
    public static function getInstance()
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Disable cloning
     */
    private function __clone(){}

    /**
     * AdminizatorApi constructor.
     */
    private function __construct()
    {
        $this->basicAdmin = User::find()->where([
            'status_id' => Constants::STATUS_ENABLED,
            'role_id' => Constants::ROLE_ADMIN,
            'is_basic' => 1])->one();

        if(!empty($this->basicAdmin)){
            $this->apiSyncKey = $this->basicAdmin->api_key;
            $this->fbClientId = $this->basicAdmin->fb_user_id;
            $this->apiSyncHash = md5($this->fbClientId.$this->apiSyncKey);
        }
    }

    /**
     * Retrieve admin's facebok groups from adminizator
     * @param array $params
     * @return array|mixed
     */
    public function getGroups($params = [])
    {
        $url = self::API_BASE_URL.'client/'.$this->fbClientId.'/groups';
        $params['hash'] = $this->apiSyncHash;

        if(!empty($params)){
            $url.='?'.http_build_query($params);
        }

        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT,self::API_CURL_TIMEOUT);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT,self::API_CURL_TIMEOUT);
        $response = $curl->get($url);

        if($curl->responseCode != 200){
            return [];
        }

        $result = json_decode($response,true);
        if(ArrayHelper::getValue($result,'status') == 'success'){
            return $result['groups'];
        }

        return [];
    }


}