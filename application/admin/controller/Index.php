<?php
/**
 * +----------------------------------------------------------------------
 * | 首页控制器
 * +----------------------------------------------------------------------
 */
namespace app\admin\controller;
use app\admin\model\Admin;
use app\admin\model\AuthRule;
use app\common\model\Users;
use think\Db;
use think\facade\Env;
use think\facade\Session;
use think\facade\Request;
use think\facade\validate;

use app\common\model\Img as M;

class Index extends Base
{
    
    //获取左侧导航
    public function index()
    {
        $authtoken = Request::param('authtoken');
        $admin_id = Db::name('admin')->where('token',$authtoken)->value('id');
        
        $group_id = Db::name('auth_group_access')->where('uid',$admin_id)->value('group_id');
            
        $rules = Db::name('auth_group')
            ->where('id',$group_id)
            ->value('rules');
            $rules = explode(',',$rules);
            
        $authRule = AuthRule::where('status',1)
            ->order('sort asc')
            ->select()
            ->toArray();

        $menus = array();
        
        
        foreach ($authRule as $key=>$val){
            $authRule[$key]['href'] = url($val['name']);
            if($val['pid']==0){
                if($admin_id!=1){
                    
                    if(in_array($val['id'],$rules)){
                        $menus[] = $val;
                    }
                }else{
                    $menus[] = $val;
                }
            }
        }
        foreach ($menus as $k=>$v){
            $menus[$k]['children']=[];
            foreach ($authRule as $kk=>$vv){
                if($v['id']==$vv['pid']){
                    if($admin_id!=1) {
                        if (in_array($vv['id'],$rules)) {
                            $menus[$k]['children'][] = $vv;
                        }
                    }else{
                        $menus[$k]['children'][] = $vv;
                    }
                }
            }
        }
        
        $data_rt['status'] = 200;
        $data_rt['msg'] = '获取成功';
        $data_rt['data'] = $menus;
        
        return json_encode($data_rt,true);
    }
    
     //获取三级导航
    public function three()
    {
        
        $authtoken = Request::param('authtoken');
        $pid = Request::param('pid');
        $admin_id = Db::name('admin')->where('token',$authtoken)->value('id');
        $group_id = Db::name('auth_group_access')->where('uid',$admin_id)->value('group_id');
            
        $rules = Db::name('auth_group')
            ->where('id',$group_id)
            ->value('rules');
            $rules = explode(',',$rules);
            
        $authRule = AuthRule::where('status',1)->where('pid',$pid)->where('auth_open',1)
            ->order('sort asc')
            ->select()
            ->toArray();
        
        $menus = array();
        
        
        foreach ($authRule as $key=>$val){
            $authRule[$key]['href'] = url($val['name']);
        
            if($admin_id!=1){
                
                if(in_array($val['id'],$rules)){
                    $menus[] = $val;
                }
            }else{
                $menus[] = $val;
            }
            
        }
        // foreach ($menus as $k=>$v){
        //     $menus[$k]['children']=[];
        //     foreach ($authRule as $kk=>$vv){
        //         if($v['id']==$vv['pid']){
        //             if($admin_id!=1) {
        //                 if (in_array($vv['id'],$rules)) {
        //                     $menus[$k]['children'][] = $vv;
        //                 }
        //             }else{
        //                 $menus[$k]['children'][] = $vv;
        //             }
        //         }
        //     }
        // }
        
        $data_rt['status'] = 200;
        $data_rt['msg'] = '获取成功';
        $data_rt['data'] = $menus;
        
        return json_encode($data_rt,true);
    }

    //右侧
    public function main()
    {
        //系统信息
        $version = Db::query('SELECT VERSION() AS ver');
        $config  = [
            'url'             => $_SERVER['HTTP_HOST'],
            'document_root'   => $_SERVER['DOCUMENT_ROOT'],
            'server_os'       => PHP_OS,
            'server_port'     => $_SERVER['SERVER_PORT'],
            'server_ip'       => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '',
            'server_soft'     => $_SERVER['SERVER_SOFTWARE'],
            'php_version'     => PHP_VERSION,
            'mysql_version'   => $version[0]['ver'],
            'max_upload_size' => ini_get('upload_max_filesize')
        ];
        //查找一周内注册用户信息
        $user = Users::where('create_time','>',time()-60*60*24*7)->count();
        //查找待处理留言信息
        $message =  Db::name('message')->where('status','0')->count();

        $this->view->assign('config'  , $config);
        $this->view->assign('user'    , $user);
        $this->view->assign('message' , $message);
        return $this->view->fetch();
    }
    
  
    //上传文件
    public function upload(){
        //file是传文件的名称，这是webloader插件固定写入的。因为webloader插件会写入一个隐藏input，不信你们可以通过浏览器检查页面
        $file = request()->file('images');
        $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move('uploads');

        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            //echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            $url =  "/uploads/".$info->getSaveName();
            
            $url = str_replace("\\","/",$url);
            
            $result['code'] =200;
            $result['msg'] = '上传成功';
            $result['data'] = $url;
            return json_encode($result,true);
            
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getFilename();
        }else{
            // 上传失败获取错误信息
            $result['code'] = 500;
            $result['msg'] = $file->getError();
            return json_encode($result,true);
        }
    }
    
    //上传文件
    public function uploads(){
        //file是传文件的名称，这是webloader插件固定写入的。因为webloader插件会写入一个隐藏input，不信你们可以通过浏览器检查页面
        $file = request()->file('images');
        $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move('uploads/thumb');

        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            //echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            $url =  "/uploads/thumb/".$info->getSaveName();
            
            $url = str_replace("\\","/",$url);
            
            $data['thumb'] = $url;
            
            $id = Db::name('img')->insertGetId($data);
            $where['id'] = $id;
            $pinfo = Db::name('img')->field('id,thumb')->where($where)->find();
            
            $request = Request::instance();
            $domain = $request->domain();
            
            $pinfo['thumb'] = $domain.$pinfo['thumb'];

            $result['code'] =200;
            $result['msg'] = '上传成功';
            $result['data'] = $pinfo;
            return json_encode($result,true);
            
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getFilename();
        }else{
            // 上传失败获取错误信息
            $result['code'] = 500;
            $result['msg'] = $file->getError();
            return json_encode($result,true);
        }
    }
    
    public function uploads_del(){
        if(Request::isPost()) {
            $id = Request::post('id');
            if(empty($id)){
                $rs_arr['status'] = 500;
        		$rs_arr['msg'] = 'ID不存在';
        		return json_encode($rs_arr,true);
        		exit;
            }
            
            $whr['id'] = $id;
            $path = Db::name('img')->where($whr)->value('thumb');
            
            $paths = Env::get('root_path').'public'.$path;
         
            if (file_exists($paths)) {
                @unlink($paths);//删除
            }
            
            $m = new M();
            $m->del($id);
            
            $rs_arr['status'] = 200;
	        $rs_arr['msg'] ='success';
    		return json_encode($rs_arr,true);
    		exit;
        }
    }


    //wangEditor
    public function wangEditor(){
        // 获取上传文件表单字段名
        $fileKey = array_keys(request()->file());
        for($i=0 ; $i<count($fileKey) ; $i++){
            // 获取表单上传文件
            $file = request()->file($fileKey[$i]);
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move('uploads');
            if($info){
                $path[]='/uploads/'.str_replace('\\','/',$info->getSaveName());
            }
        }

        if($path){
            $result['errno'] = 0;
            $result["data"] =  $path;
            return json_encode($result);

        }else{
            // 上传失败获取错误信息
            $result['code'] =1;
            $result['msg'] = '图片上传失败!';
            $result['data'] = '';
            return json_encode($result,true);
        }
    }

    //ckeditor
    public function ckeditor(){
        // 获取上传文件表单字段名
        $fileKey = array_keys(request()->file());
        for($i=0 ; $i<count($fileKey) ; $i++){
            // 获取表单上传文件
            $file = request()->file($fileKey[$i]);
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move('uploads');
            if($info){
                $path[]='/uploads/'.str_replace('\\','/',$info->getSaveName());
            }
        }
    
        if($path){
            $result['uploaded'] = true;
            $result["url"] =  $path;
            return json_encode($result);

        }else{
            // 上传失败获取错误信息
            $result['uploaded'] =false;
            $result['url'] = '';
            return json_encode($result,true);
        }
    }

    //清除缓存
    public function clear(){
        $R = Env::get('runtime_path');
        if ($this->_deleteDir($R)) {
            $result['msg'] = '清除缓存成功!';
            $result['code'] = 1;
        } else {
            $result['msg'] = '清除缓存失败!';
            $result['code'] = 0;
        }
        $result['url'] = url('admin/index/index');
        return $result;
    }

    //执行删除
    private function _deleteDir($R)
    {
        $handle = opendir($R);
        while (($item = readdir($handle)) !== false) {
            if ($item != '.' and $item != '..') {
                if (is_dir($R . '/' . $item)) {
                    $this->_deleteDir($R . '/' . $item);
                } else {
                    if($item!='.gitignore'){
                        if (!unlink($R . '/' . $item)){
                            return false;
                        }
                    }
                }
            }
        }
        closedir($handle);
        return true;
        //return rmdir($R); //删除空的目录
    }
    
    
    
    
    //修改密码
    public function resetPass(){
        $authtoken = Request::param('authtoken');
        $oldpassword = Request::param('oldpassword');
        $newpassword = Request::param('newpassword');
        $newpassword2 = Request::param('newpassword2');
        $info = Db::name('admin')->where('token',$authtoken)->find();
        
        if($newpassword != $newpassword2){
            
            $data_rt['status'] = 500;
            $data_rt['msg'] = '两次密码不一致';
            
        }else{
            
            if(md5(trim($oldpassword)) != $info['password']){
                $data_rt['status'] = 500;
                $data_rt['msg'] = '旧密码不正确';
            }else{
                
                $where['id'] = $info['id'];
                $data['password'] = md5(trim($newpassword));
                if(Admin::update($data,$where)){
                    $data_rt['status'] = 200;
                    $data_rt['msg'] = '修改成功';
                }else{
                    $data_rt['status'] = 500;
                    $data_rt['msg'] = '修改失败';
                }
            
            }
            
        }
        
        return json_encode($data_rt,true);
        
    }
    
    public function tongji(){
        
        $id = Request::param('id');
        $start = Request::param('start');
        $end = Request::param('end');
        $language = Request::param('language');
        //根据id和时间查询次数
        
        if(empty($id)){
            $where1 = [];
            
            if(isset($start)&&$start!=""&&isset($end)&&$end!="")
            {
                $where1[] = ['addtime','between',[strtotime($start)*1000,strtotime($end)*1000]];
            }
            
            $list = Db::name('product')->field('id,title')->where('language',$language)->select();
            foreach ($list as $key => $val){
                $where['productid'] = $val['id'];
                $list[$key]['clicknum'] = Db::name('tongji')->where($where)->where($where1)->count();
            }
            
            $list = sortArrByManyField($list, 'clicknum', SORT_DESC, 'id', SORT_ASC);
       
            $arr = array_slice($list,0,10);
            
            
            $data['dataX'] = array_column($arr,'title');
            $data['dataY'] = array_column($arr,'clicknum');
        
        }else{
            
            if(isset($start)&&$start!=""&&isset($end)&&$end!="")
            {
                $tlist = getDateFromRange(strtotime($start),strtotime($end));
            }else{
                $t1 = time()-2592000;
                $tlist = getDateFromRange($t1,time()+100);
            }
            $arr = [];
            foreach ($tlist as $k=>$v) {
                $arr[$k]['time'] = $v;
            }
          
            foreach ($arr as $key => $val){
                $whereq['riqi'] = $val['time'];
                $whereq['productid'] = $id;
                $arr[$key]['clicknum'] = Db::name('tongji')->where($whereq)->count();
            }
            
            $data['dataX'] = array_column($arr,'time');
            $data['dataY'] = array_column($arr,'clicknum');
            //$zlist = sortArrByManyField($arr, 'clicknum', SORT_DESC, 'time', SORT_ASC);
       
            //$zlist = array_slice($arr,0,10);
            
        }
        
        
        $data_rt['status'] = 200;
        $data_rt['msg'] = 'success';
        $data_rt['data'] = $data;
        return json_encode($data_rt,true);
        
    }
    
    
    public function plist(){
        
        $language = Request::param('language');
        if(empty($language)){
            $language = 1;
            $pid = 39;
        }else{
            if($language == 1){
                $pid = 39;
            }else{
                $pid = 96;
            }
        }
        
        
        
        $list = Db::name('cate')->field('id,catname')->where('parentid',$pid)->where('language',$language)->select();
        foreach ($list as $key => $val){
            $list2 = Db::name('cate')->field('id,catname')->where('parentid',$val['id'])->where('language',$language)->select();
            foreach ($list2 as $key2 => $val2){
                $list3 = Db::name('product')->field('id,title as catname')->where('catid',$val2['id'])->where('language',$language)->select();
                $list2[$key2]['children'] = $list3;
            }
            $list[$key]['children'] = $list2;
        }
        
        $data_rt['status'] = 200;
        $data_rt['msg'] = 'success';
        $data_rt['data'] = $list;
        return json_encode($data_rt,true);
    }
    
    public function daochu(){
        
        $id = Request::param('id');
        $start = Request::param('start');
        $end = Request::param('end');
        $language = Request::param('language');
        //根据id和时间查询次数
        
        if(empty($id)){
            $where1 = [];
            
            if(isset($start)&&$start!=""&&isset($end)&&$end!="")
            {
                $where1[] = ['addtime','between',[strtotime($start)*1000,strtotime($end)*1000]];
            }else{
                $start = '--';
                $end = date('Y-m-d H:i:s',time());
            }
            
            $list = Db::name('product')->field('id,title')->where('language',$language)->select();
            foreach ($list as $key => $val){
                $where['productid'] = $val['id'];
                $list[$key]['clicknum'] = Db::name('tongji')->where($where)->where($where1)->count();
                $list[$key]['start'] = $start;
                $list[$key]['end'] = $end;
            }
            
            $arr = sortArrByManyField($list, 'clicknum', SORT_DESC, 'id', SORT_ASC);
       
            //$arr = array_slice($list,0,10);
            
    
        }else{
            
            $title = Db::name('product')->where('id',$id)->value('title');
            
            if(isset($start)&&$start!=""&&isset($end)&&$end!="")
            {
                $tlist = getDateFromRange(strtotime($start),strtotime($end));
            }else{
                $t1 = time()-2592000;
                $tlist = getDateFromRange($t1,time()+86400);
            }
            $arr = [];
            foreach ($tlist as $k=>$v) {
                $arr[$k]['time'] = $v;
            }
          
            foreach ($arr as $key => $val){
                $whereq['riqi'] = $val['time'];
                $whereq['productid'] = $id;
                $arr[$key]['clicknum'] = Db::name('tongji')->where($whereq)->count();
                
                $arr[$key]['title'] = $title;
            }
            
            $arr = sortArrByManyField($arr, 'time', SORT_DESC);
            
        }
        
        
        $data_rt['status'] = 200;
        $data_rt['msg'] = 'success';
        $data_rt['data'] = $arr;
        return json_encode($data_rt,true);
        
    }
    

}
