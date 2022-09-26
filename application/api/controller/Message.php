<?php
/**
 * +----------------------------------------------------------------------
 * | 新闻管理控制器
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use app\common\model\Cate;
use think\Db;
use think\facade\Request;

//实例化默认模型
use app\common\model\Message as M;

class Message extends Base
{
    protected $validate = 'Message';

    public function index(){
        $data = Request::param();
        
        if(empty($data['name'])){
            $rs_arr['status'] = 201;
    		$rs_arr['msg'] = '标题不为空';
    		return json_encode($rs_arr,true);
    		exit;
        }
        if(empty($data['content'])){
            $rs_arr['status'] = 201;
    		$rs_arr['msg'] = '内容不为空';
    		return json_encode($rs_arr,true);
    		exit;
        }
        if(empty($data['language'])){
            $rs_arr['status'] = 201;
    		$rs_arr['msg'] = '语种不能为空';
    		return json_encode($rs_arr,true);
    		exit;
        }
        if($data['language'] == 1){
            if(empty($data['phone'])){
                $rs_arr['status'] = 201;
        		$rs_arr['msg'] = '手机不能为空';
        		return json_encode($rs_arr,true);
        		exit;
            }
        }else{
            if(empty($data['email'])){
                $rs_arr['status'] = 201;
        		$rs_arr['msg'] = '邮箱不能为空';
        		return json_encode($rs_arr,true);
        		exit;
            }
        }
        
        $m = new M();
        $result =  $m->addPost($data);
        if($result['error']){
            $rs_arr['status'] = 201;
    		$rs_arr['msg'] = $result['msg'];
    		return json_encode($rs_arr,true);
    		exit;
        }else{
            $rs_arr['status'] = 200;
	        $rs_arr['msg'] = $result['msg'];
    		return json_encode($rs_arr,true);
    		exit;
        }
    
    }


}
