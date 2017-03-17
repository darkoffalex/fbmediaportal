<?php
namespace app\commands;

use app\helpers\AdminizatorApi;
use app\helpers\Sort;
use app\models\Category;
use app\models\Language;
use app\models\Post;
use app\models\PostCategory;
use yii\console\Controller;
use yii\console\Exception;
use phpQuery;
use app\models\User;
use app\helpers\Constants;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class ParseController extends Controller
{

    public $url;
    public $category;
    public function options($actionID)
    {
        return ['url', 'category'];
    }


    /**
     * @var User
     */
    private $_basicAdmin;
    /**
     * Get basic admin (singleton style)
     * @return User
     */
    private function getBasicAdmin()
    {
        /* @var $basicAdmin User */
        if(empty($this->_basicAdmin)){
            $this->_basicAdmin = User::find()->where(['role_id' => Constants::ROLE_ADMIN, 'is_basic' => 1])->one();
            $this->_basicAdmin = !empty($this->_basicAdmin) ? $this->_basicAdmin : new User();
        }

        return $this->_basicAdmin;
    }


    /**
     * @var Language
     */
    private $_firstLanguage = null;
    /**
     * Get first language (singleton style)
     * @return Language
     */
    private function getFirstLanguage()
    {
        /* @var $language Language */
        if(empty($this->_firstLanguage)){
            $this->_firstLanguage = Language::find()->orderBy('id ASC')->one();
        }

        return $this->_firstLanguage;
    }

    /**
     * Get string between two sub-strings
     * @param $start
     * @param $end
     * @param $pool
     * @return string
     */
    public function getBetween($start,$end,$pool){
        $temp1 = strpos($pool,$start)+strlen($start);
        $result = substr($pool,$temp1,strlen($pool));
        $dd=strpos($result,$end);
        if($dd == 0){
            $dd = strlen($result);
        }

        return substr($result,0,$dd);
    }

    public function mb_ucfirst($text) {
        return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    /**
     * Parses http://navigator.russkievantalii.com portal page content. Builds categories and posts
     * @throws Exception
     */
    public function actionIndex()
    {
        exit("Method disabled \n");

        mb_internal_encoding("UTF-8");

        if(empty($this->url)){
            throw new Exception("URL should be set");
        }

        if(!empty($this->category) && !is_numeric($this->category)){
            throw new Exception("Category should be numeric value");
        }

        if(stripos($this->url,'navigator.russkievantalii.com') === false){
            throw new Exception("Wrong URL given");
        }

        echo "Getting main page... \n";
        $data = file_get_contents($this->url);

        echo "Parsing main page... \n";
        $document = phpQuery::newDocumentHTML($data);
        $links = $document->find('.entry-content')->find('a');

        echo "Detecting parent category... \n";
        $patentId = (int)$this->category;
        $patentId = Category::find()->where(['id' => $patentId])->count() > 0 ? $patentId : 0;

        echo "Importing first level categories... \n";
        foreach($links as $link){

            $pqLink = pq($link);

            $subUrl = $pqLink->attr('href');
            $title = $pqLink->text();

            $category = new Category();
            $category->name = $title;
            $category->parent_category_id = $patentId;
            $category->priority = Sort::GetNextPriority(Category::className(),['parent_category_id' => $patentId]);
            $category->created_at = date('Y-m-d H:i:s',time());
            $category->updated_at = date('Y-m-d H:i:s',time());
            $category->created_by_id = $this->getBasicAdmin()->id;
            $category->updated_by_id = $this->getBasicAdmin()->id;
            $category->status_id = Constants::STATUS_ENABLED;

            if($category->save()){
                echo "Category {$category->id} added to database \n";
                $trl = $category->getATrl($this->getFirstLanguage()->prefix);
                $trl -> name = $category->name;
                $trl -> isNewRecord ? $trl->save() : $trl->update();

                echo "Getting sub-page... \n";
                $data = file_get_contents($subUrl);

                echo "Parsing sub-page... \n";
                $document = phpQuery::newDocumentHTML($data);

                $pTags = $document->find('.entry-content')->find('p');
                $pParsedContent = [];
                $pFirstLevelPosts = [];
                $currentKey = null;

                foreach($pTags as $tag){
                    $html = pq($tag)->html();
                    if(strpos($html, 'strong') !== false){
                        $key = mb_strtolower($this->getBetween('<strong>','</strong>',$html));
                        $currentKey = !empty($key) ? $this->mb_ucfirst(strip_tags($key)) : $currentKey;
                    }elseif(!empty($currentKey) && strpos($html,'permalink') !== false){
                        $title = str_replace('Подробнее…','',strip_tags($html));
                        $key = $this->getBetween('permalink/','/"',$html);

                        if(!empty($key) && !empty($title)){
                            $pParsedContent[$currentKey][] = [
                                'title' => str_replace('Подробнее…','',strip_tags($html)),
                                'key' => $this->getBetween('permalink/','/"',$html)
                            ];
                        }
                    }elseif(empty($currentKey) && strpos($html,'permalink') !== false){
                        $title = str_replace('Подробнее…','',strip_tags($html));
                        $key = $this->getBetween('permalink/','/"',$html);

                        if(!empty($key) && !empty($title)){
                            $pFirstLevelPosts[] = [
                                'title' => str_replace('Подробнее…','',strip_tags($html)),
                                'key' => $this->getBetween('permalink/','/"',$html)
                            ];
                        }
                    }
                }

                if(!empty($pParsedContent)){
                    echo "Importing sub-categories... \n";

                    foreach($pParsedContent as $categoryName => $p){
                        $subCat = new Category();
                        $subCat->name = $categoryName;
                        $subCat->parent_category_id = $category->id;
                        $subCat->priority = Sort::GetNextPriority(Category::className(),['parent_category_id' => $category->id]);
                        $subCat->created_at = date('Y-m-d H:i:s',time());
                        $subCat->updated_at = date('Y-m-d H:i:s',time());
                        $subCat->created_by_id = $this->getBasicAdmin()->id;
                        $subCat->updated_by_id = $this->getBasicAdmin()->id;
                        $subCat->status_id = Constants::STATUS_ENABLED;

                        if($subCat->save()){
                            echo "Sub-category {$subCat->id} added to database \n";
                            $trl = $subCat->getATrl($this->getFirstLanguage()->prefix);
                            $trl -> name = $subCat->name;
                            $trl -> isNewRecord ? $trl->save() : $trl->update();

                            echo "Importing posts... \n";
                            foreach($p as $postInfo){
                                if(Post::find()->where(['fb_sync_id' => $postInfo['key']])->count() == 0){
                                    $post = new Post();
                                    $post->name = $postInfo['title'];
                                    $post->fb_sync_id = $postInfo['key'];
                                    $post->content_type_id = Constants::CONTENT_TYPE_NEWS;
                                    $post->status_id = Constants::STATUS_IN_STOCK;
                                    $post->type_id = Constants::POST_TYPE_IMPORTED;
                                    $post->created_at = date('Y-m-d H:i:s',time());
                                    $post->updated_at = date('Y-m-d H:i:s',time());
                                    $post->created_by_id = $this->getBasicAdmin()->id;
                                    $post->updated_by_id = $this->getBasicAdmin()->id;

                                    if($post->save()){
                                        echo "Post {$post->id} added to database \n";

                                        $cp = new PostCategory();
                                        $cp -> post_id = $post->id;
                                        $cp -> category_id = $subCat->id;
                                        $cp -> created_at = date('Y-m-d H:i:s',time());
                                        $cp -> updated_at = date('Y-m-d H:i:s',time());
                                        $cp -> created_by_id = $this->getBasicAdmin()->id;
                                        $cp -> updated_by_id = $this->getBasicAdmin()->id;
                                        $cp -> save();

                                        $trl = $post->getATrl($this->getFirstLanguage()->prefix);
                                        $trl -> name = $post->name;
                                        $trl -> isNewRecord ? $trl->save() : $trl->update();
                                    }
                                }else{
                                    echo "Post already added \n";
                                }
                            }
                        }
                    }
                }else{
                    echo "Importing posts... \n";
                    if(!empty($pFirstLevelPosts)){
                        foreach($pFirstLevelPosts AS $postInfo) {
                            if (Post::find()->where(['fb_sync_id' => $postInfo['key']])->count() == 0) {
                                $post = new Post();
                                $post->name = $postInfo['title'];
                                $post->fb_sync_id = $postInfo['key'];
                                $post->content_type_id = Constants::CONTENT_TYPE_NEWS;
                                $post->status_id = Constants::STATUS_IN_STOCK;
                                $post->type_id = Constants::POST_TYPE_IMPORTED;
                                $post->created_at = date('Y-m-d H:i:s', time());
                                $post->updated_at = date('Y-m-d H:i:s', time());
                                $post->created_by_id = $this->getBasicAdmin()->id;
                                $post->updated_by_id = $this->getBasicAdmin()->id;

                                if ($post->save()) {
                                    echo "Post {$post->id} added to database \n";

                                    $cp = new PostCategory();
                                    $cp->post_id = $post->id;
                                    $cp->category_id = $category->id;
                                    $cp->created_at = date('Y-m-d H:i:s', time());
                                    $cp->updated_at = date('Y-m-d H:i:s', time());
                                    $cp->created_by_id = $this->getBasicAdmin()->id;
                                    $cp->updated_by_id = $this->getBasicAdmin()->id;
                                    $cp->save();

                                    $trl = $post->getATrl($this->getFirstLanguage()->prefix);
                                    $trl->name = $post->name;
                                    $trl->isNewRecord ? $trl->save() : $trl->update();
                                }
                            }else{
                                echo "Post already added \n";
                            }
                        }
                    }
                }
            }
        }

        echo "Importing finished successfully! \n";
    }

    /**
     * Fix all names of posts
     */
    public function actionFixNames()
    {
        mb_internal_encoding("UTF-8");

        echo "Getting main page... \n";
        $data = file_get_contents("http://navigator.russkievantalii.com/%d0%bf%d0%be%d0%bb%d0%b5%d0%b7%d0%bd%d0%b0%d1%8f-%d0%b8%d0%bd%d1%84%d0%be%d1%80%d0%bc%d0%b0%d1%86%d0%b8%d1%8f/");

        echo "Parsing main page... \n";
        $document = phpQuery::newDocumentHTML($data);
        $links = $document->find('.entry-content')->find('a');


        echo "Processing... \n";

        foreach($links as $link){
            $pqLink = pq($link);
            $subUrl = $pqLink->attr('href');
            $title = $pqLink->text();

            if(!empty($title)){

                $categoryNames[] = $title;
                $data = file_get_contents($subUrl);
                $document = phpQuery::newDocumentHTML($data);

                $pTags = $document->find('.entry-content')->find('p');
                $pParsedContent = [];
                $pFirstLevelPosts = [];
                $currentKey = null;

                foreach($pTags as $tag){
                    $html = pq($tag)->html();
                    if(strpos($html, 'strong') !== false){
                        $key = mb_strtolower($this->getBetween('<strong>','</strong>',$html));
                        $currentKey = !empty($key) ? $this->mb_ucfirst(strip_tags($key)) : $currentKey;
                    }elseif(!empty($currentKey) && strpos($html,'permalink') !== false){
                        $title = str_replace('Подробнее…','',strip_tags($html));
                        $key = $this->getBetween('permalink/','/"',$html);

                        if(!empty($key) && !empty($title)){
                            $pParsedContent[$currentKey][] = [
                                'title' => str_replace('Подробнее…','',strip_tags($html)),
                                'key' => $this->getBetween('permalink/','/"',$html)
                            ];
                        }
                    }elseif(empty($currentKey) && strpos($html,'permalink') !== false){
                        $title = str_replace('Подробнее…','',strip_tags($html));
                        $key = $this->getBetween('permalink/','/"',$html);

                        if(!empty($key) && !empty($title)){
                            $pFirstLevelPosts[] = [
                                'title' => str_replace('Подробнее…','',strip_tags($html)),
                                'key' => $this->getBetween('permalink/','/"',$html)
                            ];
                        }
                    }
                }

                if(!empty($pParsedContent)){
                    foreach($pParsedContent as $categoryName => $p){
                        if(!empty($categoryName)){
                            foreach($p as $postInfo){
                                $theKey = $postInfo['key'];
                                $theKey = filter_var(explode('/',$theKey,2)[0], FILTER_SANITIZE_NUMBER_INT);

                                /* @var $post Post */
                                $post = Post::find()->where(['fb_sync_id' => $theKey])->one();
                                if(!empty($post)){
                                    //fix name
                                    $post->name = $this->clearName($postInfo['title']);
                                    $post->update();

                                    //update translatable data
                                    $trl = $post->getATrl('ru');
                                    $trl->name = $post->name;
                                    $trl->isNewRecord ? $trl->save() : $trl->update();

                                    echo "Post {$post->id} | {$post->fb_sync_id} | {$post->name} fixed \n";
                                }else{
                                    echo "Not found post with ID {$theKey}";
                                }
                            }
                        }
                    }
                }else{
                    if(!empty($pFirstLevelPosts)){
                        foreach($pFirstLevelPosts AS $postInfo) {
                            $theKey = $postInfo['key'];
                            $theKey = filter_var(explode('/',$theKey,2)[0], FILTER_SANITIZE_NUMBER_INT);

                            /* @var $post Post */
                            $post = Post::find()->where(['fb_sync_id' => $theKey])->one();
                            if(!empty($post)){
                                //fix name
                                $post->name = $this->clearName($postInfo['title']);
                                $post->update();

                                //update translatable data
                                $trl = $post->getATrl('ru');
                                $trl->name = $post->name;
                                $trl->isNewRecord ? $trl->save() : $trl->update();

                                echo "Post {$post->id} | {$post->fb_sync_id} | {$post->name} fixed \n";
                            }else{
                                echo "Not found post with ID {$theKey}";
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Reparses wikipedia-ra, finds only new posts
     */
    public function actionReParseFast()
    {
        mb_internal_encoding("UTF-8");

        echo "Getting main page... \n";
        $data = file_get_contents("http://navigator.russkievantalii.com/%d0%bf%d0%be%d0%bb%d0%b5%d0%b7%d0%bd%d0%b0%d1%8f-%d0%b8%d0%bd%d1%84%d0%be%d1%80%d0%bc%d0%b0%d1%86%d0%b8%d1%8f/");

        echo "Parsing main page... \n";
        $document = phpQuery::newDocumentHTML($data);
        $links = $document->find('.entry-content')->find('a');

        //list of prepared items that should be imported
        $preparedItems = [];

        echo "Importing first level categories... \n";
        foreach($links as $link){

            $categoryNames = [];
            $pqLink = pq($link);

            $subUrl = $pqLink->attr('href');
            $title = $pqLink->text();


            if(!empty($title)){

                $categoryNames[] = $title;
                echo "Category {$title} added to name list \n";

                echo "Getting sub-page... \n";
                $data = file_get_contents($subUrl);

                echo "Parsing sub-page... \n";
                $document = phpQuery::newDocumentHTML($data);

                $pTags = $document->find('.entry-content')->find('p');
                $pParsedContent = [];
                $pFirstLevelPosts = [];
                $currentKey = null;

                foreach($pTags as $tag){
                    $html = pq($tag)->html();
                    if(strpos($html, 'strong') !== false){
                        $key = mb_strtolower($this->getBetween('<strong>','</strong>',$html));
                        $currentKey = !empty($key) ? $this->mb_ucfirst(strip_tags($key)) : $currentKey;
                    }elseif(!empty($currentKey) && strpos($html,'permalink') !== false){
                        $title = str_replace('Подробнее…','',strip_tags($html));
                        $key = $this->getBetween('permalink/','/"',$html);

                        if(!empty($key) && !empty($title)){
                            $pParsedContent[$currentKey][] = [
                                'title' => str_replace('Подробнее…','',strip_tags($html)),
                                'key' => $this->getBetween('permalink/','/"',$html)
                            ];
                        }
                    }elseif(empty($currentKey) && strpos($html,'permalink') !== false){
                        $title = str_replace('Подробнее…','',strip_tags($html));
                        $key = $this->getBetween('permalink/','/"',$html);

                        if(!empty($key) && !empty($title)){
                            $pFirstLevelPosts[] = [
                                'title' => str_replace('Подробнее…','',strip_tags($html)),
                                'key' => $this->getBetween('permalink/','/"',$html)
                            ];
                        }
                    }
                }

                if(!empty($pParsedContent)){
                    echo "Parsing sub-categories... \n";

                    foreach($pParsedContent as $categoryName => $p){

                        if(!empty($categoryName)){
                            echo "Sub-category {$categoryName} added to name-list \n";

                            echo "Storing new posts... \n";
                            foreach($p as $postInfo){
                                $theKey = $postInfo['key'];
                                $theKey = filter_var(explode('/',$theKey,2)[0], FILTER_SANITIZE_NUMBER_INT);
                                if(Post::find()->where(['fb_sync_id' => $theKey])->count() == 0){

                                    $catNamesTmp = $categoryNames;
                                    $catNamesTmp[] = $categoryName;

                                    $postArr = [
                                        'title' => $this->clearName($postInfo['title']),
                                        'key' => $theKey,
                                        'categories' => $catNamesTmp
                                    ];

                                    $preparedItems[] = $postArr;
                                }else{
                                    echo "Already added \n";
                                }
                            }
                        }
                    }
                }else{
                    echo "Parsing first-level posts... \n";
                    if(!empty($pFirstLevelPosts)){
                        foreach($pFirstLevelPosts AS $postInfo) {
                            $theKey = $postInfo['key'];
                            $theKey = filter_var(explode('/',$theKey,2)[0], FILTER_SANITIZE_NUMBER_INT);
                            if (Post::find()->where(['fb_sync_id' => $theKey])->count() == 0) {

                                $postArr = [
                                    'title' => $this->clearName($postInfo['title']),
                                    'key' => $theKey,
                                    'categories' => $categoryNames
                                ];

                                $preparedItems[] = $postArr;
                            }else{
                                echo "Already added \n";
                            }
                        }
                    }
                }
            }
        }

        exit('Finished!');

        echo "ITEMS PREPARED \n\n";
        echo "Importing...\n\n";

        $total = count($preparedItems);
        $added = 0;
        $categoryAppended = 0;

        foreach ($preparedItems as $item){

            /* @var $category Category */
            $category = null;

            if(count($item['categories']) > 1){
                $category = Category::find()
                    ->alias('c')
                    ->joinWith('parent as pc')
                    ->andWhere(['like','c.name', $item['categories'][count($item['categories'])-1]])
                    ->andWhere(['like','pc.name', $item['categories'][0]])
                    ->one();
            }elseif(count($item['categories']) != 0){
                $category = Category::find()
                    ->andWhere(['like','name', $item['categories'][0]])
                    ->andWhere(['parent_category_id' => 0])
                    ->one();
            }

            echo "Adding POST {$item['key']} \n";

            //create a post
            $post = new Post();
            $post->name = $item['title'];
            $post->fb_sync_id = $item['key'];
            $post->content_type_id = Constants::CONTENT_TYPE_POST;
            $post->status_id = Constants::STATUS_IN_STOCK;
            $post->type_id = Constants::POST_TYPE_IMPORTED;
            $post->is_parsed = 0;
            $post->need_update = 1;
            $post->created_at = date('Y-m-d H:i:s', time());
            $post->updated_at = date('Y-m-d H:i:s', time());
            $post->created_by_id = $this->getBasicAdmin()->id;
            $post->updated_by_id = $this->getBasicAdmin()->id;

            if($post->save()){

                echo "POST {$item['key']} added\n";

                $added++;

                //update translatable data
                $trl = $post->getATrl('ru');
                $trl->name = $post->name;
                $trl->isNewRecord ? $trl->save() : $trl->update();

                //append category if needed
                if(!empty($category)){
                    $cp = new PostCategory();
                    $cp->post_id = $post->id;
                    $cp->category_id = $category->id;
                    $cp->created_at = date('Y-m-d H:i:s', time());
                    $cp->updated_at = date('Y-m-d H:i:s', time());
                    $cp->created_by_id = $this->getBasicAdmin()->id;
                    $cp->updated_by_id = $this->getBasicAdmin()->id;

                    if($cp->save()){
                        $categoryAppended++;
                        echo "Category appended for post {$item['key']} added\n";
                    }
                }
            }
        }

        echo "DONE! Added {$added} of {$total} posts.\nCategory has {$categoryAppended}\n";
    }


    /**
     * Cleans fucked names
     * @param $name
     * @return mixed
     */
    private function clearName($name)
    {
        $parts = explode('(',$name);
        $lastIndex = count($parts)-1;
        $name = str_replace(' ('.$parts[$lastIndex],'',$name);
        return $name;
    }
}
