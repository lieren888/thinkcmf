<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | LeoXie <380019813@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\service;

use app\cms\model\CmsArchivesModel;
use app\cms\model\CmsModelxModel;
use app\cms\model\CmsFieldsModel;
use think\db\Query;

class PostService
{
    /**
     * 文章查询
     * @param $filter
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function adminArticleList($filter)
    {
        return $this->adminPostList($filter);
    }

    /**
     * 页面文章列表
     * @param $filter
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function adminPageList($filter)
    {
        return $this->adminPostList($filter, true);
    }

    /**
     * 文章查询
     * @param      $filter
     * @param bool $isPage
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function adminPostList($filter, $isPage = false)
    {

        $join = [
            ['__USER__ u', 'a.user_id = u.id'],
            ['__CMS_CHANNEL__ b', 'a.channel_id = b.id'],
        ];
        $field = 'a.*,u.user_login,u.user_nickname,u.user_email,b.name as b_name';

        // $category = empty($filter['category']) ? 0 : intval($filter['category']);
        // if (!empty($category)) {
        //     array_push($join, [
        //         '__CMS_CHANNEL__ b', 'a.channel_id = b.id'
        //     ]);
        //     $field = 'a.*,u.user_login,u.user_nickname,u.user_email,b.name as b_name';
        // }esle{
        //     array_push($join, [
        //         '__CMS_CHANNEL__ b', 'a.channel_id = b.id'
        //     ]);
        //     $field = 'a.*,u.user_login,u.user_nickname,u.user_email,b.name as b_name';
        // }

        $delete_time =  empty($filter['delete_time']) ? array(['a.delete_time', '=', 0]) : array(['a.delete_time', '>', 0]);

        $cmsArchivesModel = new CmsArchivesModel();
        $articles = $cmsArchivesModel
                ->alias('a')
                ->field($field)
                ->join($join)
                ->where('a.create_time', '>=', 0)
                ->where($delete_time)
                ->where(function (Query $query) use ($filter, $isPage) {
                    $category = empty($filter['category']) ? 0 : intval($filter['category']);
                    if (!empty($category)) {
                        $query->where('b.id', $category);
                    }
                    $startTime = empty($filter['start_time']) ? 0 : strtotime($filter['start_time']);
                    $endTime   = empty($filter['end_time']) ? 0 : strtotime($filter['end_time']);
                    if (!empty($startTime)) {
                        $query->where('a.published_time', '>=', $startTime);
                    }
                    if (!empty($endTime)) {
                        $query->where('a.published_time', '<=', $endTime);
                    }
    
                    $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
                    if (!empty($keyword)) {
                        $query->where('a.title', 'like', "%$keyword%");
                    }
    
                    // if ($isPage) {
                    //     $query->where('a.post_type', 2);
                    // } else {
                    //     $query->where('a.post_type', 1);
                    // }
                })
                ->order('update_time', 'DESC')
                ->paginate(10);
        //echo $cmsArchivesModel->getLastSql();
        return $articles;

    }

    /**
     * 已发布文章查询
     * @param  int $postId     文章id
     * @param int  $categoryId 分类id
     * @return array|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function publishedArticle($postId, $categoryId = 0)
    {
        $cmsArchivesModel = new CmsArchivesModel();
		
		
        $post            = $cmsArchivesModel::get($postId);
        //模型
        $cmsModelxModel = new CmsModelxModel();
        $mxData = $cmsModelxModel->get($post->model_id);
        //外表
        $out_table  = strtoUpper($mxData->table);


        if (empty($categoryId)) {
            
            $join = [
                ['__USER__ user', 'user.id = a.user_id'],
                ['__'.$out_table.'__ table', 'a.id = table.id']
            ];

            $where = [
                //'a.post_type'   => 1,
                'a.status' => 1,
                'a.delete_time' => 0,
                'a.id'          => $postId
            ];

            $article = $cmsArchivesModel
				->alias('a')
				->field('a.*,user.user_nickname,table.*')
                ->join($join)
                //->with('channel')
                ->where($where)
                ->where('a.published_time', ['< time', time()], ['> time', 0], 'and')
                ->find();
        } else {
            $where = [
                //'a.post_type'       => 1,
                'a.status'     => 1,
                'a.delete_time'     => 0,
                'relation.id' => $categoryId,
                'a.id'          => $postId
            ];

            $join    = [
                ['__USER__ user', 'user.id = a.user_id'],
                ['__'.$out_table.'__ table', 'a.id = table.id'],
                ['__CMS_CHANNEL__ relation', 'a.channel_id = relation.id']
            ];
            $article = $cmsArchivesModel
				->alias('a')
				->field('a.*,user.user_nickname,table.*')
                ->join($join)
                //->with('channel')
                ->where($where)
                ->where('a.published_time', ['< time', time()], ['> time', 0], 'and')
                ->find();
        }

        //echo $cmsArchivesModel::getLastSql();

        /**
         * 转换内容
         */
        if($article){
            $values = [];
            if ($postId) {
                $values = db($mxData->table)->where('id', $postId)->find();
            }
            $fields = CmsFieldsModel::where('model_id', $post->model_id)
                    ->order('list_order asc,id desc')
                    ->select();
            foreach ($fields as $k => $v) {
                switch($v->type){
                    case "editor"://编辑器
                        $article[$v->name] = cmf_replace_content_file_url(htmlspecialchars_decode($values[$v->name]));//转码
                    break;
                    default:
                    break;
                }
            }
        }

        return $article;
    }

    /**
     * 上一篇文章
     * @param int $postId     文章id
     * @param int $categoryId 分类id
     * @return array|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function publishedPrevArticle($postId, $categoryId = 0)
    {
        $cmsArchivesModel = new CmsArchivesModel();


        $post            = $cmsArchivesModel::get($postId);
        //模型
        $cmsModelxModel = new CmsModelxModel();
        $mxData = $cmsModelxModel->get($post->model_id);
        //外表
        $out_table  = strtoUpper($mxData->table);
        

        if (empty($categoryId)) {


            $join = [
                ['__USER__ user', 'user.id = a.user_id'],
                ['__'.$out_table.'__ table', 'a.id = table.id']
            ];


            $where = [
                //'a.post_type'   => 1,
                'a.status' => 1,
                'a.delete_time' => 0,
            ];

            $article = $cmsArchivesModel
                ->alias('a')
                ->field('a.*,user.user_nickname,table.*')
                ->join($join)
                //->with('channel')
                ->where($where)
                ->where('a.id', '<', $postId)
                ->where('a.published_time', ['< time', time()], ['> time', 0], 'and')
                ->order('a.id', 'DESC')
                ->find();

        } else {
            $where = [
                //'a.post_type'       => 1,
                'a.status'     => 1,
                'a.delete_time'     => 0,
                'relation.id' => $categoryId
            ];

            $join    = [
                ['__USER__ user', 'user.id = a.user_id'],
                ['__'.$out_table.'__ table', 'a.id = table.id'],
                ['__CMS_CHANNEL__ relation', 'a.channel_id = relation.id']
            ];
            
            $article = $cmsArchivesModel
                ->alias('a')
                ->field('a.*,user.user_nickname,table.*')
                ->join($join)
                //->with('channel')
                ->where($where)
                ->where('a.id', '<', $postId)
                ->where('a.published_time', ['< time', time()], ['> time', 0], 'and')
                ->order('a.id', 'DESC')
                ->find();
        }


        /**
         * 转换内容
         */
        if($article){
            $values = [];
            if ($postId) {
                $values = db($mxData->table)->where('id', $postId)->find();
            }
            $fields = CmsFieldsModel::where('model_id', $post->model_id)
                    ->order('list_order asc,id desc')
                    ->select();
            foreach ($fields as $k => $v) {
                switch($v->type){
                    case "editor"://编辑器
                        $article[$v->name] = cmf_replace_content_file_url(htmlspecialchars_decode($values[$v->name]));//转码
                    break;
                    default:
                    break;
                }
            }
        }

        return $article;
    }

    /**
     * 下一篇文章
     * @param int $postId     文章id
     * @param int $categoryId 分类id
     * @return array|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function publishedNextArticle($postId, $categoryId = 0)
    {
        $cmsArchivesModel = new CmsArchivesModel();


        $post            = $cmsArchivesModel::get($postId);
        //模型
        $cmsModelxModel = new CmsModelxModel();
        $mxData = $cmsModelxModel->get($post->model_id);
        //外表
        $out_table  = strtoUpper($mxData->table);
        
        
        if (empty($categoryId)) {


            $join = [
                ['__USER__ user', 'user.id = a.user_id'],
                ['__'.$out_table.'__ table', 'a.id = table.id']
            ];


            $where = [
                //'a.post_type'   => 1,
                'a.status' => 1,
                'a.delete_time' => 0,
            ];

            $article = $cmsArchivesModel
                ->alias('a')
                ->field('a.*,user.user_nickname,table.*')
                ->join($join)
                //->with('channel')
                ->where($where)
                ->where('a.id', '>', $postId)
                ->where('a.published_time', ['< time', time()], ['> time', 0], 'and')
                ->order('a.id', 'ASC')
                ->find();
        } else {
            $where = [
                //'post.post_type'       => 1,
                'a.status'     => 1,
                'a.delete_time'     => 0,
                'relation.id' => $categoryId
            ];

            $join    = [
                ['__USER__ user', 'user.id = a.user_id'],
                ['__'.$out_table.'__ table', 'a.id = table.id'],
                ['__CMS_CHANNEL__ relation', 'a.channel_id = relation.id']
            ];
            $article = $cmsArchivesModel
                ->alias('a')
                ->field('a.*,user.user_nickname,table.*')
                ->join($join)
                //->with('channel')
                ->where($where)
                ->where('a.id', '>', $postId)
                ->where('a.published_time', ['< time', time()], ['> time', 0], 'and')
                ->order('a.id', 'ASC')
                ->find();
        }


        /**
         * 转换内容
         */
        if($article){
            $values = [];
            if ($postId) {
                $values = db($mxData->table)->where('id', $postId)->find();
            }
            $fields = CmsFieldsModel::where('model_id', $post->model_id)
                    ->order('list_order asc,id desc')
                    ->select();
            foreach ($fields as $k => $v) {
                switch($v->type){
                    case "editor"://编辑器
                        $article[$v->name] = cmf_replace_content_file_url(htmlspecialchars_decode($values[$v->name]));//转码
                    break;
                    default:
                    break;
                }
            }
        }

        return $article;
    }

    /**
     * 页面管理查询
     * @param int $pageId 文章id
     * @return array|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function publishedPage($pageId)
    {

        $where = [
            'status'      => 1,
            //'delete_time' => 0,
            'id'          => $pageId
        ];

        $cmsPageModel = new \app\cms\model\CmsPageModel();
        $page            = $cmsPageModel
            ->where($where)
            ->where('published_time', ['< time', time()], ['> time', 0], 'and')
            ->find();

        return $page;
    }

}