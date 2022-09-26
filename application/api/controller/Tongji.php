<?php
/**
 * +----------------------------------------------------------------------
 * | 首页控制器
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use think\Db;
use think\facade\Request;

class Tongji 
{
    
    public function index(){
        
        $data = Request::param();
        
        if(empty($data['language'])){
            $data_rt['status'] = 200;
            $data_rt['msg'] = '请选择语言';
            return json_encode($data_rt,true);
        }
        
        if(empty($data['productid'])){
            $data_rt['status'] = 200;
            $data_rt['msg'] = '商品不存在';
            return json_encode($data_rt,true);
        }else{
            $info = Db::name('product')->where('id',$data['productid'])->find();
            $data['title'] = $info['title'];
        }
        
        $sign = md5($data['oneid'].$data['twoid'].$data['productid'].$data['url'].$data['addtime'].'core2022');
        
       
        if(empty($data['sign'])){
            $data_rt['status'] = 200;
            $data_rt['msg'] = '无签名';
            return json_encode($data_rt,true);
        }else{
            if($sign != $data['sign']){
                $data_rt['status'] = 200;
                $data_rt['msg'] = '签名不正确';
                return json_encode($data_rt,true);
            }else{
                unset($data['sign']);
                $data['riqi'] = date('Y-m-d',time());
                Db::name('tongji')->insert($data);
                $data_rt['status'] = 200;
                $data_rt['msg'] = 'success';
                return json_encode($data_rt,true);
            }
        }
        
        
        
    }
    
}
