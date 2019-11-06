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
namespace app\cms\controller;

use cmf\controller\HomeBaseController;
use app\cms\model\CmsChannelModel;
use app\cms\service\PostService;
use app\cms\model\CmsArchivesModel;
use think\Db;

class ArchivesController extends HomeBaseController
{
    /**
     * 文章详情
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {

        $cmsArchivesModel = new CmsArchivesModel();
        $postService         = new PostService();

        $articleId  = $this->request->param('id', 0, 'intval');
        $categoryId = $this->request->param('cid', 0, 'intval');
        $article    = $postService->publishedArticle($articleId, $categoryId);

        if (empty($article)) {
            abort(404, '文章不存在!');
        }


        $prevArticle = $postService->publishedPrevArticle($articleId, $categoryId);
        $nextArticle = $postService->publishedNextArticle($articleId, $categoryId);

        $tplName = 'show';

        if (empty($categoryId)) {
            $categories = $article['channel']->ToArray();

            if (count($categories) > 0) {
                $this->assign('category', $categories);
            } else {
                abort(404, '文章未指定分类!');
            }

        } else {
            $cmsChannelModel = new CmsChannelModel();
            $category = $cmsChannelModel->where('id', $categoryId)->where('status', 1)->find();

            if (empty($category)) {
                abort(404, '文章不存在!');
            }

            $this->assign('category', $category);

            $tplName = empty($category["showtpl"]) ? $tplName : $category["showtpl"];
        }

        Db::name('cms_archives')->where('id', $articleId)->setInc('hits');


        hook('cms_before_assign_archives', $article);

        $this->assign('article', $article);
        $this->assign('prev_article', $prevArticle);
        $this->assign('next_article', $nextArticle);

        //$tplName = empty($article['more']['template']) ? $tplName : $article['more']['template'];

        return $this->fetch("/$tplName");
    }

    // // 文章点赞
    // public function doLike()
    // {
    //     $this->checkUserLogin();
    //     $articleId = $this->request->param('id', 0, 'intval');


    //     $canLike = cmf_check_user_action("posts$articleId", 1);

    //     if ($canLike) {
    //         Db::name('portal_post')->where('id', $articleId)->setInc('post_like');

    //         $this->success("赞好啦！");
    //     } else {
    //         $this->error("您已赞过啦！");
    //     }
    // }

}
