<?php

namespace app\api\controller;
use think\Controller;
use think\facade\Hook;
use think\facade\Request;
use think\facade\Session;
use think\Db;

class Base extends Controller
{
    
    protected $admin_id = NULL,$encryption = null;
	
    //初始化方法
    public function initialize()
    {
        
        header('Content-Type: text/html;charset=utf-8');
    	header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
    	header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
		
        //Hook::listen("admin_log");
    }
    
    //空操作
    public function _empty(){
        if(Request::isAjax()){
            return ['error'=>1,'msg'=>'操作方法为空'];
        }else{
            $this->error('操作方法为空');
        }
    }
}
