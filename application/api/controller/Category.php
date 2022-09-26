<?php

namespace app\api\controller;
use think\Db;
use think\facade\Request;

//实例化默认模型
use app\common\model\Cate as C;
use app\common\model\Module as M;

class Category extends Base
{
    protected $validate = 'Cate';
    
     //权限列表
    public function index(){
        
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
            $where[] = ['parentid','=','0'];
            
            $list = Db::name('cate')->where($where)->order('sort asc')->select();
            
            foreach ($list as $key => $val){
                
                $num = Db::name('cate')->where(['parentid'=>$val['id']])->count();
                if($num > 0){
                    $list[$key]['children'] = self::get_trees($val['id']);
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
    
    public function get_trees($pid = 0){
      
        $list = Db::name('cate')->where(['parentid'=>$pid])->order('sort asc')->select();
        
        foreach ($list as $key => $val){
            
            $num = Db::name('cate')->where(['parentid'=>$val['id']])->count();
            
            if($num > 0){
                $list[$key]['children'] = self::get_trees($val['id']);
            }else{
                $list[$key]['children'] = '';
            }
            
        }
        
        return $list;
    }

    public function banner(){
      
        if(Request::isPost()){
            
            $data = Request::post();
            $bannerids = C::where('id' ,'=', $data['id'])->value('bannerids');
            
            $bids = explode(',',$bannerids);
            
            $where[]=['id', 'in', $bids];
            $result = Db::name('ad')->where($where)->order('sort asc')->where('type',$data['type'])->select();
            
            $data_rt['status'] = 200;
            $data_rt['msg'] = '获取成功';
            $data_rt['data'] = $result;
            return json_encode($data_rt);
            exit;
            
        }
        
    }
    

    // //批量删除栏目
    // public function selectDel(){
    //     if(Request::isPost()) {
    //         $id = Request::post('id');
    //         if( empty($id) ){
    //             return ['error'=>1,'msg'=>'ID不存在'];
    //         }
    //         C::destroy($id);
    //         return ['error'=>0,'msg'=>'删除成功!'];
    //     }
    // }

    // //栏目排序
    // public function sort(){
    //     if(Request::isPost()){
    //         $id = Request::param('id');
    //         $sort = Request::param('sort');
    //         if (empty($id)){
    //             return ['error'=>1,'msg'=>'ID不存在'];
    //         }
    //         C::where('id',$id)
    //             ->setField('sort', $sort);
    //         return ['error'=>0,'msg'=>'修改成功!'];
    //     }
    // }

    //设置导航显示
    // public function isMenu(){
    //     if(Request::isPost()){
    //         $id = Request::param('id');
    //         if (empty($id)){
    //             return ['error'=>1,'msg'=>'ID不存在'];
    //         }

    //         $info = C::get($id);
    //         $is_menu = $info['is_menu']==1?0:1;

    //         C::where('id',$id)
    //             ->setField('is_menu', $is_menu);

    //         return ['error'=>0,'msg'=>'修改成功!'];
    //     }
    // }

    //设置跳转下级
    // public function isNext(){
    //     if(Request::isPost()){
    //         $id = Request::param('id');
    //         if (empty($id)){
    //             return ['error'=>1,'msg'=>'ID不存在'];
    //         }

    //         $info = C::get($id);
    //         $is_next = $info['is_next']==1?0:1;

    //         C::where('id',$id)
    //             ->setField('is_next', $is_next);

    //         return ['error'=>0,'msg'=>'修改成功!'];
    //     }
    // }
}
