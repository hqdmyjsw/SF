<?php
namespace controllers;
use dao\CategoryDao;
use dao\PostDao;


class PostController extends \SF\Controllers\BaseController{

    // 列表

    public function __construct()
    {
       $categoryLst =  CategoryDao::get()->toArray();

       $this->categoryArr = [];
       foreach ($categoryLst as $category){
           $this->categoryArr[$category['_id'].""] = $category['name'];
       }
    }

    /**
     * @Route (value='/posts')
     */
    public function posts(){
        $page = empty($_REQUEST['page'])?1:$_REQUEST['page'];
        $limit = 3;
        $list = [];
        $publish_type = intval($_REQUEST['publish_type']);

        $postMode =  PostDao::where(['publish_type'=>$publish_type])->offset(($page-1)*$limit)->limit($limit)->get();

        if($postMode == false){
            return $this->toJson([],-1);
        }

        $postLst = $postMode->toArray();

        foreach ($postLst as $post){
                array_push($list,[
                    'id' => $post['_id']."",
                    'publish'=> ['nickname'=>'丢丢君'],
                    'publish_time' => '一天前',
                    'content' => $post['content'],
                    'attachment' => ['http://img.taopic.com/uploads/allimg/120727/201995-120HG1030762.jpg'],
                    'address' => $post['address'],
                    'type' => $this->categoryArr[$post['entity_class']],
                    'looks' => $post['looks'],
                ]);
        }

       return $this->toJson($list);
    }

    /**
     * 新建一个 posts
     * @Route (value='/posts',method='POST')
     */
    public function addPost(){
        if($_REQUEST['publish_type'] == 1){
            $publish_type= 1;
            $tag = '拾';
        }
        if($_REQUEST['publish_type'] == 2){
            $publish_type= 2;
            $tag = '丢';
        }
        $data = [
            'publish_uid'=> $_REQUEST['puid'],
            'used_uid'=> '',
            'publish_type'=> $publish_type,//1 失物招领 2 寻物启事
            'publish_time' => time(),
            'content' => $_REQUEST['content'],
            'attachment' => [],
            'address' => $_REQUEST['address'],
            'addressDetail' => $_REQUEST['addressDetail'],
            'location' => [
                'lat' => '','lng'=>''
            ],
            'staus' => 0, // 0 新创建 1 已认领 (已归还） 2 (已确认) 3
            'amount' => $_REQUEST['amount'], // 悬赏金额
            'entity_class' => $_REQUEST['entity_class'],
            'looks' => 0,
            'tags' => [], // 设置标签
        ];
        array_push($data['tags'],$tag);

        $id = PostDao::insert($data);

        return $this->toJson(['id'=>$id.""],0,'发布成功');
    }

    /**
     * 删除一个post
     * @Route (value='/posts',method='DELETE')
     */
    public function delPost($res,$req,$id){

    }

    /**
     * @Route (value='/posts/{id}/count',method='GET')
     */
    public function postsCount($req,$res,$id){

        PostDao::where(['_id'=>PostDao::format($id)])->update([

        ]);
        return $this->toJson([]);
    }



    /**
     * @Route (value='/posts/{id}',method='GET')
     */
    public function postsByid($req,$res,$id){

       $post =  PostDao::where(['_id'=>PostDao::format($id)])->find()->toArray();
        $data = [
            'publish'=> ['nickname'=>'丢丢君'],
            'publish_time' => date('Y-m-d H:i:s',$post['publish_time']),
            'content' => $post['content'],
            'attachment' => ['http://img.taopic.com/uploads/allimg/120727/201995-120HG1030762.jpg'],
            'address' => $post['address'],
            'type' => $this->categoryArr[$post['entity_class']],
            'looks' => $post['looks'],
            'amount' => $post['amount'],
            'tags'=> @$post['tags']
        ];
        return $this->toJson($data);
    }


}