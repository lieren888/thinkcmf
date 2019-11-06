<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leoxie <380019813@qq.com>
// +----------------------------------------------------------------------
namespace plugins\leoxie_switch_lang;
use cmf\lib\Plugin;
use think\Config;
use think\Cookie;
use think\Log;

class LeoxieSwitchLangPlugin extends Plugin
{
    public $info = [
        'name'        => 'LeoxieSwitchLang',//Demo插件英文名，改成你的插件英文就行了
        'title'       => '多语言模板设置',
        'description' => '多语言模板设置',
        'status'      => 1,
        'author'      => 'LeoXie',
        'version'     => '1.0.0',
        'demo_url'    => 'http://demo.xiechihua.com',
        'author_url'  => 'http://www.xiechihua.com'
    ];

    public $hasAdmin = 0;//插件是否有后台管理界面

    // 插件安装
    public function install()
    {
        return true;//安装成功返回true，失败false
    }

    // 插件卸载
    public function uninstall()
    {
        return true;//卸载成功返回true，失败false
    }

    //实现的switch_theme钩子方法
    public function switchTheme($param)
    {
        //获取配置信息
        $config = $this->getConfig();

        //参考
        $langSet = config('default_lang'); 
        if (isset($_GET['l'])) {//语言参数   
            $langSet = strtolower($_GET['l']);
            cookie('thinkvar', $langSet, 3600);
        }elseif (cookie('thinkvar')) {
            $langSet = strtolower(cookie('thinkvar'));
        }else{
            //增加
            $langSet = empty($config['default_lang']) ? $langSet : $config['default_lang'];
        }

        ////////////////////////正常业务//////////////////////
        $newTheme = '';
        if(cmf_is_mobile()){//当前访问是手机端
            if($langSet=='en-us'){//英文版
                $newTheme = empty($config['mobile_theme_en']) ? '' : $config['mobile_theme_en'];
            }else if($langSet=='zh-cn'){//中文版
                $newTheme = empty($config['mobile_theme']) ? '' : $config['mobile_theme'];
            }else{}
        }
        else
        { 
            if($langSet=='en-us'){//英文版
                $newTheme = empty($config['pc_theme_en']) ? '' : $config['pc_theme_en'];
            }else if($langSet=='zh-cn'){//中文版
                $newTheme = empty($config['pc_theme']) ? '' : $config['pc_theme'];
            }else{}
        }

        return $newTheme;
    }
    
}