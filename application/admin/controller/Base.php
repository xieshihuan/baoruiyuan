<?php

namespace app\admin\controller;
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
		
		$token=\request()->header('token');    //从header里获取token
	    
		//检查token
	    if($token){
	        
            $admin = $this->CheckToken($token);
            
            if($admin['status'] != 200){
                
                echo json_encode($admin,true);
                die;
            }else{
                
                //定义方法白名单
                $allow = [
                    'Index/index',      //首页左侧导航
                    'Index/three',      //获取三级导航
                    'Index/main',       //右侧
                    'Index/upload',     //上传文件
                    'Index/uploads',     //上传多文件
                    'Index/uploads_del',     //上传多文件
                    'Index/wangEditor', //编辑器
                    'Index/ckeditor',   //编辑器
                    'Index/clear',      //清除缓存
                    'Index/logout',     //退出登录
                    'Index/resetpass',  //重置密码
                    'Login/index',      //登录页面
                    'Login/checkLogin', //校验登录
                    'Login/captcha',    //登录验证码
                    'Index/plist',    //登录验证码
                    'Index/tongji',    //登录验证码
                    
                ];
        
                //查找当前控制器和方法，控制器首字母大写，方法首字母小写 如：Index/index
                $route = Request::controller() . '/' . lcfirst(Request::action());
                //print_r($allow);
                //die;
                //权限认证
                if(!in_array($route, $allow)){
                    
                    if($admin['data']!=1){
                        //开始认证
                        $auth = new \Auth\Auth();
                        $result = $auth->check($route,$admin['data']);
                        if(!$result){
                            $data_rt['status']= 501;
                			$data_rt['msg']='您无此操作权限';
                			echo json_encode($data_rt,true);
	                        die;
                        }
                    }
                }
               
            }
            
	    }else{
	        
	        $data_rt['status'] = 99901;
			$data_rt['msg'] = '请先登录';
			echo json_encode($data_rt,true);
	        die;
	    }
	    
        //Hook::listen("admin_log");
    }
    
    /**
	 * 检查token
	 */
	public function CheckToken($token){
	    
		if($token){
			$res = Db::name('admin')
				->field('id,expires_time')
				->where(['token'=>$token])
				->where('expires_time','>',time())
				->find();
			
			if ($res){
				$this->admin_id = $res['id'];
				//更新token,到期十分钟更新
				if($res['expires_time']-time() <= 10*60){
					$expires_time = $res['expires_time']+3600;
					Db::name('admin')->where('id',$res['id'])->update(['expires_time'=>$expires_time]);
				}
				$rs_arr['status'] = 200;
				$rs_arr['msg']='验证通过';
				$rs_arr['data']=$res['id'];
				return $rs_arr;
				die;
			}else{
				$rs_arr['status'] = 500;
				$rs_arr['msg']= '登录已过期，请重新登录';
				return $rs_arr;die;
			}
			
		}else{
			$rs_arr['status']=500;
			$rs_arr['msg']='请先登录';
			return $rs_arr;die;
		}
		
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
