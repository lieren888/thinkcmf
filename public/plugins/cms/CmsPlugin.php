<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | LeoXie <380019813@qq.com>
// +----------------------------------------------------------------------

namespace plugins\cms;

use cmf\lib\Plugin;
use think\Db;
use think\Loader;
use ZipArchive;

class CmsPlugin extends Plugin
{
    public $info = [
        'name'        => 'Cms', 
        'title'       => 'CMS内容管理系统',
        'description' => 'CMS内容管理系统（带模板展示）',
        'status'      => 1,
        'author'      => '猎仁',
        'version'     => '1.0.0',
        'demo_url'    => 'http://www.lightseacloud.com',
        'author_url'  => 'http://www.lightseacloud.com',
    ];

    // 插件安装
    public function install()
    {
        $nameCStyle = Loader::parseName($this->getName());//获取当前插件类名称
        $getPluginPath = $this->getPluginPath();//获取插件根目录绝对路径

        /************************** 获取文件夹绝对路径 **************************/
        $app = $getPluginPath.'app/';
        $admin_themes = $getPluginPath.'admin_themes/';
        $themes = $getPluginPath.'themes/';
        $upload = $getPluginPath.'upload/';
        $zipfix = '.zip';

        /************************** 循环读取文件 **************************/
        $appDirs = cmf_scan_dir($app."*".$zipfix);
        $adminThemesDirs = cmf_scan_dir($admin_themes."*".$zipfix);
        $themesDirs = cmf_scan_dir($themes."*".$zipfix);
        $uploadDirs = cmf_scan_dir($upload."*".$zipfix);
        //插件里面的【appDirs】、【admin_themes】文件夹里面没有包含zip文件
        if(count($appDirs)==0 || count($adminThemesDirs)==0){
            return false; 
        }
        //压缩文件必须有且一个
        if(count($appDirs)!=1 || count($adminThemesDirs)!=1){
            return false; 
        }


        /************************** 1.app应用文件 **************************/
        $app_new = APP_PATH.$nameCStyle.$zipfix;
        //copy($app.$appDirs[0], $app_new);//测试
        $copy[] = Array($app.$appDirs[0], $app_new);

         /************************** 2.后台模板文件 **************************/
        $admin_themes_new = WEB_ROOT.'themes/admin_simpleboot3/'.$nameCStyle.$zipfix;
        $copy[] = Array($admin_themes.$adminThemesDirs[0], $admin_themes_new);

        /************************** 3.前台模板文件（可能有多个文件） **************************/
        foreach($themesDirs as $v){
            $themes =  WEB_ROOT.'plugins/'.$nameCStyle.'/themes/'.$v;
            $themes_new = WEB_ROOT.'themes/'.$v;
            $copy[] = Array($themes, $themes_new);
        }

        /************************** 4.上传文件 **************************/
        $upload_new = WEB_ROOT.'upload/'.$uploadDirs[0];
        $copy[] = Array($upload.$uploadDirs[0], $upload_new);

        /************************** 循环拷贝文件 **************************/
        foreach($copy as $v){
            copy($v[0], $v[1]);
        }

        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;//新建一个ZipArchive的对象
            /*
            通过ZipArchive的对象处理zip文件
            $zip->open这个方法的参数表示处理的zip文件名。
            如果对zip文件对象操作成功，$zip->open这个方法会返回TRUE
            */

            //1.app文件
            if ($zip->open($app_new) === TRUE)
            {
                $new_dir = str_replace($zipfix, "", $app_new);
                if (!file_exists($new_dir)){
                    mkdir($new_dir, 0777);
                }
                $zip->extractTo($new_dir);//解压
                $zip->close();//关闭处理的zip文件
                unlink($app_new);//删除文件
            }

            //2.后台模板文件
            if ($zip->open($admin_themes_new) === TRUE)
            {
                $new_dir = str_replace($zipfix, "", $admin_themes_new);
                if (!file_exists($new_dir)){
                    mkdir($new_dir, 0777);
                }
                $zip->extractTo($new_dir);
                $zip->close();//关闭处理的zip文件
                unlink($admin_themes_new);//删除文件
            }

            //3.前台模板文件（可能有多个文件）
            foreach($themesDirs as $v){
                $themes_new = WEB_ROOT.'themes/'.$v;
                if ($zip->open($themes_new) === TRUE)
                {
                    $new_dir = str_replace($zipfix, "", $themes_new);
                    if (!file_exists($new_dir)){
                        mkdir($new_dir, 0777);
                    }
                    $zip->extractTo($new_dir);//解压
                    $zip->close();//关闭处理的zip文件
                    unlink($themes_new);//删除文件
                }
            }

            //4.上传文件
            if ($zip->open($upload_new) === TRUE)
            {
                $new_dir = WEB_ROOT.'upload';
                if (!file_exists($new_dir)){
                    mkdir($new_dir, 0777);
                }
                $zip->extractTo($new_dir);//假设解压缩到在当前路径下
                $zip->close();//关闭处理的zip文件
                unlink($upload_new);//删除文件
            }
        }

		$prefix = config('database.prefix');//数据库表前缀
        try{
            //执行sql
            $sql = cmf_split_sql(APP_PATH.$nameCStyle.'/data/'.$nameCStyle.'.sql', $prefix);
            foreach($sql as $v) {
                Db::execute($v);
            }
        } catch (\Exception $e) {
            $this->dropTable();//卸载已安装的表
            return false;
        }
        return true; //安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        //$result = $this->dropTable() && $this->delData();
        $result = $this->dropTable();
        if($result==true){
            cmf_clear_cache();//清除缓存
        }
        return $result;
    }

    //卸载表
    public function dropTable(){
        $nameCStyle = Loader::parseName($this->getName());//获取当前插件类名称
        $prefix = config('database.prefix');//数据库表前缀
        try{
            //执行sql
            $sql = cmf_split_sql(APP_PATH.$nameCStyle.'/data/uninstall.sql', $prefix);
            foreach($sql as $v) {
                Db::execute($v);
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;//卸载成功返回true，失败false
    }

    //删除后台菜单表数据
    public function delData(){
        $nameCStyle = Loader::parseName($this->getName());
        try{
            // Db::name('admin_menu')->where('app',$nameCStyle)->delete();//删除菜单
            // Db::name('theme')->where('theme','cruiseship')->delete();//删除模板
            // Db::name('theme_file')->where('theme','cruiseship')->delete();//删除模板明细
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    //获取当前后台主题名
    public function adminInit($param){
        $theme = config('template.cmf_admin_default_theme');
        return $theme;
    }

    // //卸载文件
    // public function delAll(){
    //     try{
    //         $nameCStyle = Loader::parseName($this->getName());

    //         $app_menu = CMF_ROOT.'app/';
    //         $app_new = $app_menu.$nameCStyle.'.zip';//新位置
    //         $this->delDirAndFile($app_new);//app

    //         $admin_themes_menu = WEB_ROOT.'themes/admin_simpleboot3/';
    //         $admin_themes_new = $admin_themes_menu.$nameCStyle;//新位置
    //         $this->delDirAndFile($admin_themes_new);//admin_themes

    //         $themes_menu = WEB_ROOT.'themes/';
    //         $themes_new = $themes_menu.'cruiseship';//新位置
    //         $this->delDirAndFile($themes_new);//themes
    //     } catch (\Exception $e) {
    //         return false;
    //     }
    //     return true;
    // }

    // //循环删除目录和文件函数
    // function delDirAndFile( $dirName )
    // {
    //     if ( $handle = opendir( "$dirName" ) ) {
    //         while ( false !== ( $item = readdir( $handle ) ) ) {
    //             if ( $item != "." && $item != ".." ) {
    //                 if ( is_dir( "$dirName/$item" ) ) {
    //                     delDirAndFile( "$dirName/$item" );
    //                 } else {
    //                     if( unlink( "$dirName/$item" ) )echo "成功删除文件： $dirName/$item<br />\n";
    //                 }
    //             }
    //         }
    //         closedir( $handle );
    //         if( rmdir( $dirName ) )echo "成功删除目录： $dirName<br />\n";
    //     }
    // }
}
