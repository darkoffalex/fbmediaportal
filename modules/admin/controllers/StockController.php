<?php

namespace app\modules\admin\controllers;

use app\helpers\AdminizatorApi;
use app\helpers\Constants;
use app\helpers\Help;
use app\models\Post;
use app\models\PostCategory;
use app\models\PostGroup;
use app\models\StockRecommendation;
use app\models\Language;
use app\models\PostSearch;
use yii\bootstrap\ActiveForm;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class StockController extends Controller
{
    /**
     * Render list of all posts in the stock
     * @param string $type
     * @return string
     */
    public function actionIndex($type = 'main')
    {
        /* @var $languages Language[] */
        $languages = Language::find()->all();
        $lng = empty($lng) ? $languages[0]->prefix :$lng;


        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$lng,true,$type);
        return $this->render('index', compact('searchModel','dataProvider','lng','type'));
    }

    /**
     * Change status
     * @param $id
     * @param $status
     * @param null $index
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionStatus($id, $status, $index = null)
    {
        $availableStatuses = [
            Constants::STATUS_DELETED,
            Constants::STATUS_IN_STOCK
        ];

        /* @var $post Post */
        $post = Post::find()->where(['id' => (int)$id])->one();

        if(empty($post) || !in_array($status,$availableStatuses)){
            throw new NotFoundHttpException('Post not found',404);
        }

        $post->status_id = $status;
        $post->update();

        return $this->redirect(Yii::$app->request->referrer.(!empty($index) ? "#element-move-$index" : ''));
    }

    /**
     * Move to archive several items per one time
     * @param $ids
     * @return Response
     */
    public function actionBatchArchive($ids)
    {
        $ids = explode(',',$ids);
        Post::updateAll(['status_id' => Constants::STATUS_DELETED],['id' => $ids]);
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Configure recommendation rules
     * @return string
     */
    public function actionRecommendSettings()
    {
        $model = new StockRecommendation();

        if(Yii::$app->request->isPost && $model->load(Yii::$app->request->post())){
            if($model->validate()){

                if($model->reason_type_id == Constants::OFFER_REASON_AUTHOR && empty($model->author_id)){
                    $model->addError('author_id',Yii::t('admin','This field is required'));
                }elseif($model->reason_type_id == Constants::OFFER_REASON_GROUP && empty($model->group_id)){
                    $model->addError('group_id',Yii::t('admin','This field is required'));
                }elseif($model->reason_type_id == Constants::OFFER_REASON_CAT_TAG && empty($model->category_tag)){
                    $model->addError('category_tag',Yii::t('admin','This field is required'));
                }

                if(!$model->hasErrors()){
                    $model->created_at = date('Y-m-d H:i:s');
                    $model->updated_at = date('Y-m-d H:i:s');
                    $model->created_by_id = Yii::$app->user->id;
                    $model->updated_by_id = Yii::$app->user->id;
                    $model->save();
                }
            }
        }

        $all = StockRecommendation::find()->all();

        return $this->renderAjax('_recommend_settings',compact('model','all'));
    }

    /**
     * Delete recommendation rule
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function actionDeleteRecommend($id)
    {
        /* @var $model StockRecommendation */
        $model = StockRecommendation::findOne((int)$id);

        if(!empty($model)){
            $model->delete();
        }

        return $this->actionRecommendSettings();
    }

    /**
     * Change status of group (ajax)
     * @param $id
     * @param int $status
     * @return string
     */
    public function actionGroupStatus($id,$status = 0)
    {
        /* @var $group PostGroup */
        $group = PostGroup::find()->where(['id' => (int)$id])->one();

        if(!empty($group)){
            $group->stock_enabled = (int)$status;
            $group->updated_by_id = Yii::$app->user->id;
            $group->updated_at = date('Y-m-d H:i:s',time());
            $group->update();
            return 'OK';
        }

        return 'ERROR';
    }

    /**
     * Synchronizes groups with adminizator
     * @return string
     */
    public function actionSyncGroups()
    {
        //get groups from adminizator
        $groupsArray = AdminizatorApi::getInstance()->getGroups();

        //if group array is not empty - update
        if(!empty($groupsArray)){
            //set all as not synchronized
            PostGroup::updateAll(['stock_sync' => 0]);

            //pass through given groups
            foreach ($groupsArray as $groupItem){
                /* @var $group PostGroup */
                $group = PostGroup::find()->where(['fb_sync_id' => $groupItem['facebook_id']])->one();
                if(!empty($group)){
                    $group->name = $groupItem['title'];
                    $group->updated_at = date('Y-m-d H:i:s',time());
                    $group->stock_sync = 1;
                    $group->update();
                }else{
                    $group = new PostGroup();
                    $group->fb_sync_id = $groupItem['facebook_id'];
                    $group->is_group = 1;
                    $group->stock_enabled = 0;
                    $group->name = $groupItem['title'];
                    $group->url = "https://www.facebook.com/groups/{$groupItem['facebook_id']}/";
                    $group->created_at = date('Y-m-d H:i:s',time());
                    $group->updated_at = date('Y-m-d H:i:s',time());
                    $group->stock_sync = 1;
                    $group->created_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                    $group->updated_by_id = AdminizatorApi::getInstance()->basicAdmin->id;
                    $group->save();
                }
            }

            $groups = PostGroup::find()
                ->where(['is_group' => 1])
                ->all();
        }


        return $this->renderAjax('_sync_groups',compact('groups'));
    }

    /**
     * Shows groups settings
     * @return string
     */
    public function actionGroups()
    {
        $groups = PostGroup::find()
            ->where(['is_group' => 1])
            ->all();

        return $this->renderAjax('_sync_groups',compact('groups'));
    }

    /**
     * Moving post from stock to main list
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionMove($id)
    {
        $model = Post::findOne((int)$id);

        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','Not found'),404);
        }

        //set scrolling position in session (information about anchor)
        $index = Yii::$app->request->get('index');
        if(!empty($index)){
            Yii::$app->session->setFlash('scroll-to',"#element-move-$index");
        }

        //ajax validation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if($model->load(Yii::$app->request->post())){
            if($model->validate()){

                //update categories (delete ignored)
                foreach($model->categories as $category){
                    if(!in_array($category->id,$model->categoriesChecked)){
                        PostCategory::deleteAll(['post_id' => $model->id, 'category_id' => $category->id]);
                    }
                }

                //update categories (add checked)
                $current = ArrayHelper::map($model->categories,'id','id');
                foreach($model->categoriesChecked as $catID){
                    if(!in_array($catID,$current)){
                        $pc = new PostCategory();
                        $pc->category_id = $catID;
                        $pc->post_id = $model->id;
                        $pc->save();
                    }
                }

                $model->updated_at = date('Y-m-d H:i:s',time());
                $model->updated_by_id = Yii::$app->user->id;
                $model->update();

                /* @var $firstLng Language */
                $firstLng = Language::find()->orderBy('id ASC')->one();
                if(!empty($firstLng)){
                    $trl = $model->getATrl($firstLng->prefix);
                    $trl -> name = $model->name;
                    $trl -> isNewRecord ? $trl->save() : $trl->update();
                }

                $model->updateSearchKeywords();

                //update trails and sibling flags
                $model->updateTrails(false);
                $model->updateInSiblingForCat();


                if(!empty($model->author)){
                    $model->author->refreshTimeLine();
                }

                //clear cache
                //Yii::$app->cache->flush();

                return $this->redirect(Yii::$app->request->referrer.Yii::$app->session->getFlash('scroll-to'));
            }
        }

        return $this->renderAjax('_move',compact('model'));
    }
}