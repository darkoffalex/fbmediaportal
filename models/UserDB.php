<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $name
 * @property string $surname
 * @property string $email
 * @property string $fb_user_id
 * @property string $fb_avatar_url
 * @property string $avatar_file
 * @property integer $role_id
 * @property integer $type_id
 * @property integer $status_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $last_online_at
 * @property integer $created_by_id
 * @property integer $updated_by_id
 * @property integer $counter_comments
 * @property integer $counter_posts
 *
 * @property Post[] $posts
 */
class UserDB extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['auth_key', 'password_hash', 'password_reset_token', 'fb_user_id', 'fb_avatar_url'], 'string'],
            [['role_id', 'type_id', 'status_id', 'created_by_id', 'updated_by_id', 'counter_comments', 'counter_posts'], 'integer'],
            [['created_at', 'updated_at', 'last_online_at'], 'safe'],
            [['username', 'name', 'surname', 'email', 'avatar_file'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'name' => 'Name',
            'surname' => 'Surname',
            'email' => 'Email',
            'fb_user_id' => 'Fb User ID',
            'fb_avatar_url' => 'Fb Avatar Url',
            'avatar_file' => 'Avatar File',
            'role_id' => 'Role ID',
            'type_id' => 'Type ID',
            'status_id' => 'Status ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'last_online_at' => 'Last Online At',
            'created_by_id' => 'Created By ID',
            'updated_by_id' => 'Updated By ID',
            'counter_comments' => 'Counter Comments',
            'counter_posts' => 'Counter Posts',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['author_id' => 'id']);
    }
}
