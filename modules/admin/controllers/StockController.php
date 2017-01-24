<?php

namespace app\modules\admin\controllers;

use app\helpers\Constants;
use app\models\StockRecommendation;
use app\models\Language;
use app\models\PostSearch;
use yii\web\Controller;
use Yii;


class StockController extends Controller
{
    /**
     * Render list of all posts in the stock
     * @return string
     */
    public function actionIndex()
    {
        /* @var $languages Language[] */
        $languages = Language::find()->all();
        $lng = empty($lng) ? $languages[0]->prefix :$lng;


        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams,$lng,true);
        return $this->render('index', compact('searchModel','dataProvider','lng'));
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
}