<?php

namespace app\modules\admin\controllers;

use app\helpers\Constants;
use app\models\UserSearch;
use Yii;
use yii\db\Query;
use yii\helpers\Url;
use app\models\User;
use app\models\LoginForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class UsersController extends Controller
{
    /**
     * Render list of all users registered in system
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', compact('searchModel','dataProvider'));
    }

    /**
     * Creating a new user
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        //new empty user model
        $model = new User();

        //if something coming from POST
        if (Yii::$app->request->isPost) {

            //load POST data to model
            $model->load(Yii::$app->request->post());

            //if password was set
            if(!empty($model->password)){
                $model->setPassword($model->password);
                $model->generateAuthKey();
            }

            //creation scenario (password is necessary)
            $model->scenario = 'create';

            //if all data is valid
            if($model->validate()){

                //set some statistic information
                $model->created_at = date('Y-m-d H:i:s',time());
                $model->updated_at = date('Y-m-d H:i:s',time());
                $model->created_by_id = Yii::$app->user->id;
                $model->updated_by_id = Yii::$app->user->id;

                //user not imported (from adminizator), but created manually
                $model->type_id = Constants::USR_TYPE_CREATED;

                //enabled by default
                $model->status_id = Constants::STATUS_ENABLED;

                //insert it to database
                $model->save();

                //clear cache
                Yii::$app->cache->flush();

                //go to list
                return $this->redirect(Url::to(['/admin/users/index']));
            }
        }


        //render form
        return $this->render('edit',compact('model'));
    }

    /**
     * Update existing user
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate($id)
    {
        //get user by id
        /* @var $model User */
        $model = User::findOne($id);

        //if not found in database
        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','User not found'),404);
        }

        //if something coming from POST
        if (Yii::$app->request->isPost) {

            //old password
            $oldPassHash = $model->password_hash;
            $oldAuthKey = $model->auth_key;

            //load POST data to model
            $model->load(Yii::$app->request->post());

            //if password was set - generate
            if(!empty($model->password)){
                $model->setPassword($model->password);
                $model->generateAuthKey();
            //if not - set old password
            }else{
                $model->password_hash = $oldPassHash;
                $model->auth_key = $oldAuthKey;
            }

            //if all data is valid
            if($model->validate()){

                //set some statistic information
                $model->updated_at = date('Y-m-d H:i:s',time());
                $model->updated_by_id = Yii::$app->user->id;

                //update in database
                $model->update();

                //clear cache
                Yii::$app->cache->flush();
            }
        }

        //render form
        return $this->render('edit',compact('model'));
    }

    /**
     * Delete existing user
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        //get user by id
        /* @var $model User */
        $model = User::findOne($id);

        //if user not found or if user is current
        if(empty($model) || $model->id == Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('admin','User not found'),404);
        }

        //delete from db
        $model->delete();

        //clear cache
        Yii::$app->cache->flush();

        //back to list
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Change status of existing user
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionStatus($id)
    {
        //get user by id
        /* @var $model User */
        $model = User::findOne($id);

        //if user not found or if user is current
        if(empty($model) || $model->id == Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('admin','User not found'),404);
        }

        //change status of user to opposite and update
        $model->status_id = $model->status_id == Constants::STATUS_ENABLED ? Constants::STATUS_DISABLED : Constants::STATUS_ENABLED;
        $model->updated_by_id = Yii::$app->user->id;
        $model->updated_at = date('Y-m-d H:i:s',time());
        $model->update();

        //clear cache
        Yii::$app->cache->flush();

        //back to list
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Small preview of users (basic information) for modal windows
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPreview($id)
    {
        //get user by id
        /* @var $model User */
        $model = User::findOne((int)$id);

        //if user not found or if user is current
        if(empty($model)){
            throw new NotFoundHttpException(Yii::t('admin','User not found'),404);
        }

        return $this->renderAjax('_preview',compact('model'));
    }

    /**
     * Ajax search method for auto-complete fields
     * @param null $q
     * @param null $id
     * @return array
     */
    public function actionAjaxSearch($q = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $out = ['results' => ['id' => '', 'text' => '']];

        $words = explode(' ',$q,2);

        if (!is_null($q)) {
            $query = new Query();
            $query->select('id, name, surname, username')->from('user');

            if(count($words) > 1){
                $query->where(['like','name',$words[0]])
                    ->andWhere(['like','surname',$words[1]])
                    ->limit(20);
            }else{
                $query->where(['like','name', $q])
                    ->orWhere(['like','surname',$q])
                    ->orWhere(['like','username', $q])
                    ->limit(20);
            }

            $command = $query->createCommand();
            $data = array_values($command->queryAll());
            $tmp = [];

            foreach($data as $index => $arr){
                $tmp[] = ['id' => $arr['id'], 'text' => $arr['name'].' '.$arr['surname']];
            }

            $out['results'] = $tmp;
        }
        elseif ($id > 0) {
            $user = User::findOne((int)$id);
            if(!empty($user)){
                $out['results'] = ['id' => $id, 'text' => $user->name];
            }
        }
        return $out;
    }
}