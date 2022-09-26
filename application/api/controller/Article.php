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
use app\common\model\Article as M;

class Article extends Base
{
    protected $validate = 'Article';

    //列表
    public function gongsidongtai(){
        
        $language = Request::param('language');
        
        if($language){
            if($language == 'cn'){
                $where['language'] = 1;
                $where2['language'] = 1;
                $where['catid'] = 92;
                $where2['catid'] = 93;
                $list = Db::name('article')
                    ->order('fabu_time asc')
                    ->where($where)
                    ->select();
                
                $list2 = Db::name('article')
                    ->order('fabu_time desc')
                    ->where($where2)
                    ->select();
                
                $list_rt['jinqi'] = $list;
                $list_rt['wangqi'] = $list2;
                
                $rs_arr['status'] = 200;
        		$rs_arr['msg'] = 'success';
        		$rs_arr['data'] = $list_rt;
        		return json_encode($rs_arr,true);
        		exit;
            }else{
                $where['language'] = 2;
                $where['catid'] = 99;
                $list = Db::name('article')
                ->order('fabu_time asc')
                ->where($where)
                ->select();
                $rs_arr['status'] = 200;
        		$rs_arr['msg'] = 'success';
        		$rs_arr['data'] = $list;
        		return json_encode($rs_arr,true);
        		exit;
            }
        }else{
            $where['language'] = 1;
            $where2['language'] = 1;
            $where['catid'] = 92;
            $where2['catid'] = 93;
            $list = Db::name('article')
                ->order('fabu_time asc')
                ->where($where)
                ->select();
            
            $list2 = Db::name('article')
                ->order('fabu_time desc')
                ->where($where2)
                ->select();
            
            $list_rt['jinqi'] = $list;
            $list_rt['wangqi'] = $list2;
            
            $rs_arr['status'] = 200;
    		$rs_arr['msg'] = 'success';
    		$rs_arr['data'] = $list_rt;
    		return json_encode($rs_arr,true);
    		exit;
        }
        
        //调取列表
        
        
    }
    
    public function hangyexinwen(){
        
        $language = Request::param('language');
        
        if($language){
            if($language == 'cn'){
                $where['language'] = 1;
                $where['catid'] = 83;
            }else{
                $where['language'] = 2;
                $where['catid'] = 104;
            }
        }else{
            $where['language'] = 1;
            $where['catid'] = 83;
        }
        //调取列表
        $list = Db::name('article')
            ->order('fabu_time desc')
            ->where($where)
            ->select();
        
        $rs_arr['status'] = 200;
		$rs_arr['msg'] = 'success';
		$rs_arr['data'] = $list;
		return json_encode($rs_arr,true);
		exit;
    }
    
    public function faguiwenjian(){
        
        $language = Request::param('language');
        
        if($language){
            if($language == 'cn'){
                $where['language'] = 1;
                $where['catid'] = 85;
            }else{
                $where['language'] = 2;
                $where['catid'] = 103;
            }
        }else{
            $where['language'] = 1;
            $where['catid'] = 85;
        }
        
        //调取列表
        $list = Db::name('article')
            ->order('fabu_time desc')
            ->where($where)
            ->select();
        
        $rs_arr['status'] = 200;
		$rs_arr['msg'] = 'success';
		$rs_arr['data'] = $list;
		return json_encode($rs_arr,true);
		exit;
    }
    
    //详情
    public function detail(){
        
        if(Request::isPost()){
            
            $data = Request::post();
            $id = $data['id'];
         
            $pinfo = Db::name('article')->alias('a')
            ->leftJoin('cate at','a.catid = at.id')
            ->field('a.*,at.catname as catname,at.url as url')->where(['a.id'=>$id])->find();
            
            $whrss['id'] = $pinfo['catid'];
            $pid = Db::name('cate')->where($whrss)->value('parentid');
            $pinfo['pcatid'] = Db::name('cate')->where(['id'=>$pid])->value('id');
            $pinfo['pcatname'] = Db::name('cate')->where(['id'=>$pid])->value('catname');
            $pinfo['purl'] = Db::name('cate')->where(['id'=>$pid])->value('url');
                
                
            $data_rt['status'] = 200;
            $data_rt['msg'] = '获取成功';
            $data_rt['data'] = $pinfo;
            return json_encode($data_rt);
            exit;
        }
        
        
    }

   
}
