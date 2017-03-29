<?php

namespace app\models;

use app\helpers\Help;
use Yii;
use yii\base\NotSupportedException;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\IdentityInterface;
use app\helpers\Constants;

class User extends UserDB implements IdentityInterface
{
    public $password;

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status_id' => Constants::STATUS_ENABLED]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status_id' => Constants::STATUS_ENABLED]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status_id' => Constants::STATUS_ENABLED,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $baseLabels = parent::attributeLabels();
        $baseLabels['password'] = 'Password';
        foreach($baseLabels as $attribute => $label){
            $baseLabels[$attribute] = Yii::t('admin',$label);
        }
        return $baseLabels;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['username', 'unique'];
        $rules[] = ['password', 'required', 'on' => 'create'];
        $rules[] = ['password', 'string', 'min' => 6];

        return $rules;
    }

    /**
     * Finds out if user has access to admin panel
     * @return bool
     */
    public function hasAdminAccess()
    {
        if($this->status_id != Constants::STATUS_ENABLED){
            return false;
        }

        return in_array($this->role_id,[
            Constants::ROLE_ADMIN,
            Constants::ROLE_REDACTOR
        ]);
    }

    /**
     * Returns URL path to user avatar
     * @return string
     */
    public function getAvatar()
    {
        return !empty($this->avatar_file) && !empty($this->last_online_at) ? $this->avatar_file : Url::to('@web/frontend/images/avatars/'.rand(1,24).'.png');
    }

    /**
     * Updates user's time-line (non-static variant)
     */
    public function refreshTimeLine()
    {
        self::refreshTimeLineStatic($this->id);
    }

    /**
     * Updates user's time-line (full refresh, deletes old data and creates new)
     * @param $userId
     * @throws \yii\db\Exception
     */
    public static function refreshTimeLineStatic($userId)
    {
        UserTimeLine::deleteAll(['user_id' => $userId]);

        $commentQuery = new Query();
        $comments = $commentQuery->select('comment.id, comment.created_at as published_at')
            ->from('comment')->leftJoin('post','comment.post_id = post.id')
            ->where('comment.author_id = :author AND post.status_id = :status',['author' => $userId, 'status' => Constants::STATUS_ENABLED])
            ->createCommand()->queryAll();

        $postsQuery = new Query();
        $posts = $postsQuery->select('id, published_at')
            ->from('post')
            ->where(['status_id' => Constants::STATUS_ENABLED, 'author_id' => $userId])
            ->createCommand()->queryAll();

        $rows = [];
        foreach ($comments as $row){
            $rows[] = [
                'user_id' => $userId,
                'post_id' => null,
                'comment_id' => $row['id'],
                'published_at' => $row['published_at']
            ];
        }

        foreach ($posts as $row){
            $rows[] = [
                'user_id' => $userId,
                'post_id' => $row['id'],
                'comment_id' => null,
                'published_at' => $row['published_at']
            ];
        }

        if(!empty($rows)){
            Yii::$app->db->createCommand()
                ->batchInsert(UserTimeLine::tableName(),['user_id','post_id','comment_id','published_at'],$rows)
                ->execute();
        }
    }
}
