<?php
namespace app\widgets;

use app\helpers\Constants;
use app\models\Category;
use app\models\Post;
use yii\base\Widget;
use yii\helpers\Html;

class MostCommentedWidget extends Widget
{
    /* @var Post */
    private $posts = [];

    public $minComments = 20;

    public $minDate = '2014.01.01 00:00:00';

    public $limit = 5;

    public function init()
    {

        //TODO: should thing about better solution. This shit fucks the performance

        $sql = "SELECT * FROM
                (SELECT *, (SELECT COUNT(*) FROM `comment` WHERE `comment`.post_id = post.id) comment_count
                FROM post WHERE post.status_id = :status
                ) p WHERE p.comment_count > :minComments AND p.updated_at > :minDate ORDER BY p.comment_count DESC LIMIT :limit";

        $this->posts = Post::findBySql($sql,[
            'minComments' => $this->minComments,
            'status' => Constants::STATUS_ENABLED,
            'minDate' => $this->minDate,
            'limit' => $this->limit])
            ->with(['trl','comments','postImages'])
            ->all();
    }

    public function run()
    {
        return $this->render('most_commented',['posts' => $this->posts]);
    }
}