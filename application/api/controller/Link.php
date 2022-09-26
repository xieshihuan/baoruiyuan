<?php
/**
 * +----------------------------------------------------------------------
 * | 友情链接控制器
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use think\facade\Request;
use think\Db;

//实例化默认模型
use app\common\model\Link as M;

class Link extends Base
{
    protected $validate = 'Link';

    //列表
    public function index(){
        
        $data = Request::post();
        
        $language = $data['language'];
        
        $number = $data['number'];
        
        //全局查询条件
        if(empty($number)){
            $number = 12;
        }
        
        $where=[];
        if($language){
            if($language == 'cn'){
                $where[] = ['language','=','1'];
            }else{
                $where[] = ['language','=','2'];
            }
        }else{
            $where[] = ['language','=','1'];
        }
        
        //调取列表
        $list = Db::name('link')
            ->field('*')
            ->where($where)
            ->order('sort ASC,id DESC')
            ->limit(0,$number)
            ->select();
            
        $rs_arr['status'] = 200;
		$rs_arr['msg'] = 'success';
		$rs_arr['data'] = $list;
		return json_encode($rs_arr,true);
		exit;
    }


}
