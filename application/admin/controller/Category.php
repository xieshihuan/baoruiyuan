<?php

namespace app\admin\controller;
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
            
            $parentid = $data['parentid'];
            $language = $data['language'];
            $moduleid = $data['moduleid'];
            
            $where=[];
            if($parentid){
                $where[]=['parentid', '=', $parentid];
            }else{
                $where[] = ['parentid','=','0'];
            }
            
            if($language){
                $where[] = ['language','=',$language];
            }else{
                $where[] = ['language','=','1'];
            }
            
            if($moduleid){
                $where[] = ['moduleid','=',$moduleid];
            }
            
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



    //添加保存
    public function addPost(){
        
        if(Request::isPost()){
            $data = Request::except('file');
            
            $result = $this->validate($data,$this->validate);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $data_rt['status'] = 500;
                $data_rt['msg'] = $result;
                return json_encode($data_rt);
                exit;
            }else{
                
                $result = C::create($data);
                if($result->id){
                    $data_rt['status'] = 200;
                    $data_rt['msg'] = '添加成功';
                    return json_encode($data_rt);
                    exit;
                }else{
                    $data_rt['status'] = 500;
                    $data_rt['msg'] = '添加失败';
                    return json_encode($data_rt);
                    exit;
                }
            }
        }
    }

    //修改保存
    public function editPost(){
        if(Request::isPost()) {
            $data = Request::except('file');
            $result = C::where('id' ,'=', $data['id'])
                ->update($data);
        
            $data_rt['status'] = 200;
            $data_rt['msg'] = '修改成功';
            return json_encode($data_rt);
            exit;
        
        }
    }
    
    public function bannerPost(){
        
        if(Request::isPost()) {
            $data = Request::post();
            $result = C::where('id' ,'=', $data['id'])
                ->update($data);
        
            $data_rt['status'] = 200;
            $data_rt['msg'] = '修改成功';
            return json_encode($data_rt);
            exit;
        }else{
            $id = input('id');
            $result = C::where('id' ,'=', $id)
                ->value('bannerids');
        
            $data_rt['status'] = 200;
            $data_rt['msg'] = '获取成功';
            $data_rt['data'] = $result;
            return json_encode($data_rt);
            exit;
        }
        
    }
    

    //删除栏目
    public function del(){
        if(Request::isPost()) {
            $id = Request::post('id');
            if( empty($id) ){
                $data_rt['status'] = 500;
                $data_rt['msg'] = 'ID不存在';
                return json_encode($data_rt);
                exit;
            }
            C::destroy($id);
            $data_rt['status'] = 200;
            $data_rt['msg'] = '删除成功';
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
