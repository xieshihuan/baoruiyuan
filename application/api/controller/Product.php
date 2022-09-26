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
use app\common\model\Product as M;

class Product extends Base
{
    protected $validate = 'Product';

    //列表
    public function index(){
        
        if(Request::isPost()){
            
            $data = Request::post();
            $id = $data['id'];
            
            $where=[];
            
            if($id){
               $where[] = ['id','=',$id];
            }else{
                $whr[] = ['moduleid','=','2'];
                $whr[] = ['parentid','=','0'];
                
                $pid = Db::name('cate')->where($whr)->value('id');
                $zid = Db::name('cate')->where($wheres)->order('sort asc')->value('id');
                
                $where[] = ['id','=',$zid];
            }
            
            $list = Db::name('cate')->where($where)->order('sort asc')->select();
            
            foreach ($list as $key => $val){
                
                $num = Db::name('cate')->where(['parentid'=>$val['id']])->count();
                if($num > 0){
                    $lists = Db::name('cate')->where(['parentid'=>$val['id']])->order('sort asc')->select();
                    foreach ($lists as $keys => $vals){
                
                        $nums = Db::name('product')->where(['catid'=>$vals['id']])->count();
                        if($num > 0){
                            $listss = Db::name('product')->where(['catid'=>$vals['id']])->order('sort asc')->select();
                            
                            foreach ($listss as $keyss => $valss){
                                $whrss['id'] = $valss['catid'];
                                $pid = Db::name('cate')->where($whrss)->value('parentid');
                                $listss[$keyss]['pcatname'] = Db::name('cate')->where(['id'=>$pid])->value('catname');
                            }
                            
                            $lists[$keys]['alist'] = $listss;
                        }else{
                            $lists[$keys]['alist'] = '';
                        }
                        
                    }
                    $list[$key]['children'] = $lists;
                }else{
                    $list[$key]['children'] = '';
                }
                
            }
            
            $data_rt['status'] = 200;
            $data_rt['msg'] = '获取成功';
            $data_rt['data'] = $list;
            return json_encode($data_rt);
            exit;
        }
        
        
    }
    
    //列表
    public function mindex(){
        //条件筛选
        $id = Request::param('id');
        //全局查询条件
        $where=[];
        if(!empty($id)){
            $where[]=['catid', '=', $id];
        }else{
            $rs_arr['status'] = 200;
    		$rs_arr['msg'] = 'pid not found';
    		$rs_arr['data'] = $list;
    		return json_encode($rs_arr,true);
    		exit;
        }
        
        //显示数量
        $pageSize = Request::param('page_size') ? Request::param('page_size') : config('page_size');
   
        //调取列表
        $list = Db::name('product')
            ->order('sort ASC')
            ->where($where)
            ->paginate($pageSize,false,['query' => request()->param()]);
        
       
        $rs_arr['status'] = 200;
		$rs_arr['msg'] = 'success';
		$rs_arr['data'] = $list;
		return json_encode($rs_arr,true);
		exit;
    }
    
    //列表
    public function search(){
        //条件筛选
        $keyword = Request::param('keyword');
        $language = Request::param('language');
        
        $where=[];
        if($language){
            if($language == 'cn'){
                $where[] = ['a.language','=','1'];
            }else{
                $where[] = ['a.language','=','2'];
            }
        }else{
            $where[] = ['a.language','=','1'];
        }
        
        if(!empty($keyword)){
            $klist = explode('_',$keyword);
            foreach ($klist as $key => $val){
                $where[]=['a.title|a.keywords', 'like', '%'.$val.'%'];
            }
        }else{
            $rs_arr['status'] = 500;
    		$rs_arr['msg'] = '请输入查询内容';
    		return json_encode($rs_arr,true);
    		exit;
        }
        
        
        //显示数量
        $pageSize = Request::param('page_size') ? Request::param('page_size') : config('page_size');
        $this->view->assign('pageSize', page_size($pageSize));

        //调取列表
        $list = Db::name('product')
            ->alias('a')
            ->leftJoin('cate at','a.catid = at.id')
            ->field('a.*,at.catname as cate_name')
            ->order('a.title ASC')
            ->where($where)
            ->paginate($pageSize,false,['query' => request()->param()]);
        
        $rs_arr['status'] = 200;
		$rs_arr['msg'] = 'success';
		$rs_arr['data'] = $list;
		return json_encode($rs_arr,true);
		exit;
    }
  
    
    //备份
    public function index——bf(){
        
        if(Request::isPost()){
            
            $data = Request::post();
            $language = $data['language'];
            
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
            
            $where[] = ['moduleid','=','2'];
            $where[] = ['parentid','=','0'];
            
            $pid = Db::name('cate')->where($where)->value('id');
            
            $wheres=[];
            $wheres[] = ['parentid','=',$pid];
            $list = Db::name('cate')->where($wheres)->order('sort asc')->select();
            
            foreach ($list as $key => $val){
                
                $num = Db::name('cate')->where(['parentid'=>$val['id']])->count();
                if($num > 0){
                    $lists = Db::name('cate')->where(['parentid'=>$val['id']])->order('sort asc')->select();
                    foreach ($lists as $keys => $vals){
                
                        $nums = Db::name('product')->where(['catid'=>$vals['id']])->count();
                        if($num > 0){
                            $listss = Db::name('product')->where(['catid'=>$vals['id']])->order('sort asc')->select();
                            
                            $lists[$keys]['alist'] = $listss;
                        }else{
                            $lists[$keys]['alist'] = '';
                        }
                        
                    }
                    $list[$key]['children'] = $lists;
                }else{
                    $list[$key]['children'] = '';
                }
                
            }
            
            $data_rt['status'] = 200;
            $data_rt['msg'] = '获取成功';
            $data_rt['data'] = $list;
            return json_encode($data_rt);
            exit;
        }
        
        
    }
    
    
    //详情
    public function detail(){
        
        if(Request::isPost()){
            
            $data = Request::post();
            $id = $data['id'];
         
            $pinfo = Db::name('product')->alias('a')
            ->leftJoin('cate at','a.catid = at.id')
            ->field('a.*,at.catname as catname')->where(['a.id'=>$id])->find();
            
            $whrss['id'] = $pinfo['catid'];
            $pid = Db::name('cate')->where($whrss)->value('parentid');
            $pinfo['pcatid'] = Db::name('cate')->where(['id'=>$pid])->value('id');
            $pinfo['pcatname'] = Db::name('cate')->where(['id'=>$pid])->value('catname');
                
            $data_rt['status'] = 200;
            $data_rt['msg'] = '获取成功';
            $data_rt['data'] = $pinfo;
            return json_encode($data_rt);
            exit;
        }
        
        
    }



}
