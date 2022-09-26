<?php
/**
 * +----------------------------------------------------------------------
 * | 首页控制器
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use app\api\model\Admin;
use app\api\model\AuthRule;
use app\common\model\Users;
use think\Db;
use think\facade\Env;
use think\facade\Session;
use think\facade\Request;
use think\facade\validate;


class Index 
{
    public function index(){
        $durl = 'https://tool.bitefu.net/qreader/index.php?url=http://wenhuaadmin.coretest.vip/uploads/20220329/d10900cd19b0d0582812d3b2d3917db2.jpeg&reload=0';
        
        $aa = curlGet($durl);
        $aa = json_decode($aa,true);
        print_r($aa);
        die;
   
    }
    
    public function ceshi(){    
        $a = xfyun();
        $b = json_decode($a,true);
        print_r($b);
        die;
    }
    
    public function sysinfo(){
        
        $data = Request::post();
        
        $language = $data['language'];
        
        $where=[];
        $wheres=[];
        if($language){
            if($language == 'cn'){
                $where[] = ['language','=','1'];
                $wheres[] = ['language','=','1'];
                $wheress[] = ['language','=','1'];
                $system = \app\common\model\System::get(1);
            }else{
                $where[] = ['language','=','2'];
                $wheres[] = ['language','=','2'];
                $wheress[] = ['language','=','2'];
                $system = \app\common\model\System::get(2);
            }
        }else{
            $where[] = ['language','=','1'];
            $wheres[] = ['language','=','1'];
            $wheress[] = ['language','=','1'];
            $system = \app\common\model\System::get(1);
        }
        
        $where[] = ['typeid','=','1'];
        $hzlist = Db::name('link')
            ->field('*')
            ->where($where)
            ->order('sort ASC,id DESC')
            ->limit(0,100)
            ->select();
            
        $wheres[] = ['typeid','=','2'];
        $linklist = Db::name('link')
            ->field('*')
            ->where($wheres)
            ->order('sort ASC,id DESC')
            ->limit(0,100)
            ->select();
            
        $wheress[] = ['moduleid','=','2'];
        $wheress[] = ['parentid','=','0'];
        $pid = Db::name('cate')->where($wheress)->value('id');
        
        $wherep=[];
        $wherep[] = ['parentid','=',$pid];
        $catelist = Db::name('cate')->where($wherep)->order('sort asc')->select();
        
            
        $data_rt['banquan'] = $system;
        $data_rt['hezuo'] = $hzlist;
        $data_rt['link'] = $linklist;
        $data_rt['cate'] = $catelist;
        
        $rs_arr['status'] = 200;
		$rs_arr['msg'] = 'success';
		$rs_arr['data'] = $data_rt;
		return json_encode($rs_arr,true);
		exit;
        
    }
}
