<?php
/**
 * +----------------------------------------------------------------------
 * | 登录制器
 * +----------------------------------------------------------------------
 */
namespace app\admin\controller;
use app\common\model\System;
use think\Controller;
use app\admin\model\Admin;
use think\captcha\Captcha;
use think\facade\Session;
use think\facade\Request;
use think\facade\Cache;
use think\Db;

class Login extends Controller
{
    


    //校验登录
    public function checkLogin(){
        $m = new Admin();
        return $m->checkLogin();
    }
    
      
    //验证码
    public function captcha(){
        
        $config =    [
            // 验证码字体大小
            'fontSize'    =>    30,
            // 验证码位数
            'length'      =>    4,
            // 关闭验证码杂点
            'useNoise'    =>    true,
            // 是否画混淆曲线
            'useCurve' => false,
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }
    
    
    //退出登录
    public function logout(){
        $authtoken = Request::param('authtoken');
        $admin_id = Db::name('admin')->where('token',$authtoken)->value('id');
        
        if($admin_id){
            $where['id'] = $admin_id;
            $data['token'] = '';
            if(Admin::update($data,$where)){
                $data_rt['status'] = 200;
                $data_rt['msg'] = '退出成功';
            }else{
                $data_rt['status'] = 500;
                $data_rt['msg'] = '退出失败';
            }
        }else{
            $data_rt['status'] = 500;
            $data_rt['msg'] = '用户不存在';
        }
        
        return json_encode($data_rt,true);
        
    }

}
