<?php
/**
 * +----------------------------------------------------------------------
 * | 会员列表控制器
 * +----------------------------------------------------------------------
 */
namespace app\admin\controller;
use app\common\model\UsersType;
use think\Db;
use think\facade\Request;

//实例化默认模型
use app\common\model\Users as M;

use PHPExcel_IOFactory;
use PHPExcel;

class Users extends Base
{
    protected $validate = 'Users';

    //列表
    public function index(){
        //条件筛选
        $keyword = Request::param('keyword');
        $this->view->assign('keyword',$keyword);

        $typeId = Request::param('type_id');
        $this->view->assign('typeId',$typeId);
        //全局查询条件
        $where=[];
        if(!empty($keyword)){
            $where[]=['u.email|u.mobile', 'like', '%'.$keyword.'%'];
        }
        if(!empty($typeId)){
            $where[]=['ut.id', '=', $typeId];
        }
        //显示数量
        $pageSize = Request::param('page_size') ? Request::param('page_size') : config('page_size');
        $this->view->assign('pageSize', page_size($pageSize));

        //调用会员组列表
        $usersType = UsersType::select();
        $this->view->assign('usersType' , $usersType);

        //调取列表
        $list = Db::name('users')
            ->alias('u')
            ->leftJoin('users_type ut','u.type_id = ut.id')
            ->field('u.*,ut.name as type_name')
            ->order('u.id DESC')
            ->where($where)
            ->paginate($pageSize,false,['query' => request()->param()]);
        $page = $list->render();
        $this->view->assign('page' , $page);
        $this->view->assign('list' , $list);
        $this->view->assign('empty', empty_list(11));
        return $this->view->fetch();
    }

    //添加
    public function add(){
        $usersType = UsersType::where('status','=',1)
            ->select();
        $this->view->assign('usersType',$usersType);
        $this->view->assign('info',null);
        return $this->view->fetch();
    }

    //添加保存
    public function addPost(){
        if(Request::isPost()) {
            $data = Request::param();
            $result = $this->validate($data,$this->validate);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }else{
                if(empty($data['password'])){
                    $this->error('请填写密码');
                }
                $data['last_login_time'] = time();
                $data['create_ip'] = $data['last_login_ip'] = Request::ip();
                $data['password'] = md5($data['password']);
                $m = new M();
                $result =  $m->addPost($data);
                if($result['error']){
                    $this->error($result['msg']);
                }else{
                    $this->success($result['msg'],'index');
                }
            }
        }
    }

    //修改
    public function edit(){
        $id = Request::param('id');
        if( empty($id) ){
            return ['error'=>1,'msg'=>'ID不存在'];
        }
        $usersType = UsersType::all();
        $this->view->assign('usersType',$usersType);

        $m = new M();
        $info = $m->edit($id);

        $this->view->assign('info', $info);
        return $this->view->fetch('add');
    }

    //修改保存
    public function editPost(){
        if(Request::isPost()) {
            $data = Request::param();
            $result = $this->validate($data,$this->validate);
            if (true !== $result) {
                // 验证失败 输出错误信息
                $this->error($result);
            }else{
                if($data['password']) {
                    $data['password'] = md5($data['password']);
                }else{
                    unset($data['password']);
                }
                $m = new M();
                $result =  $m->editPost($data);
                if($result['error']){
                    $this->error($result['msg']);
                }else{
                    $this->success($result['msg'],'index');
                }
            }
        }
    }

    //删除
    public function del(){
        if(Request::isPost()) {
            $id = Request::post('id');
            if( empty($id) ){
                return ['error'=>1,'msg'=>'ID不存在'];
            }
            $m = new M();
            return $m->del($id);
        }
    }

    //批量删除
    public function selectDel(){
        if(Request::isPost()) {
            $id = Request::post('id');
            if (empty($id)) {
                return ['error'=>1,'msg'=>'ID不存在'];
            }
            $m = new M();
            return $m->selectDel($id);
        }

    }

    //状态
    public function state(){
        if(Request::isPost()){
            $id = Request::post('id');
            if (empty($id)){
                return ['error'=>1,'msg'=>'ID不存在'];
            }
            $m = new M();
            return $m->state($id);
        }


    }

    //下载
    public function download(){
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类
        $PHPExcel->setActiveSheetIndex(0); //设置sheet的起始位置
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('用户列表'); //给当前活动sheet设置名称

        $PHPSheet
            ->setCellValue('A1','ID')
            ->setCellValue('B1','邮箱账号')
            ->setCellValue('C1','性别')
            ->setCellValue('D1','注册时间')
            ->setCellValue('E1','注册IP')
            ->setCellValue('F1','最后登录时间')
            ->setCellValue('G1','最后登录IP')
            ->setCellValue('H1','QQ')
            ->setCellValue('I1','手机号')
            ->setCellValue('J1','是否认证手机号')
            ->setCellValue('K1','是否认证邮箱')
            ->setCellValue('L1','用户组')
            ->setCellValue('M1','状态')
        ;

        //调取列表
        $list = Db::name('users')
            ->alias('u')
            ->leftJoin('users_type ut','u.type_id = ut.id')
            ->field('u.*,ut.name as type_name')
            ->order('u.id DESC')
            ->select();
        foreach ($list as $k=>$v){
            $v['sex']              = $v['sex']=='1'              ? '男'    : '女';
            $v['mobile_validated'] = $v['mobile_validated']=='1' ? '已认证' : '未认证';
            $v['email_validated']  = $v['email_validated']=='1'  ? '已认证' : '未认证';
            $v['status']           = $v['status']=='1'           ? '正常'   : '禁用';
            $v['create_time']      = date("Y-m-d H:i",$v['create_time']);
            $v['last_login_time']  = date("Y-m-d H:i",$v['last_login_time']);
            $PHPSheet
                ->setCellValue('A'.($k+2),$v['id'])
                ->setCellValue('B'.($k+2),$v['email'])
                ->setCellValue('C'.($k+2),$v['sex'])
                ->setCellValue('D'.($k+2),$v['create_time'])
                ->setCellValue('E'.($k+2),$v['create_ip'])
                ->setCellValue('F'.($k+2),$v['last_login_time'])
                ->setCellValue('G'.($k+2),$v['last_login_ip'])
                ->setCellValue('H'.($k+2),$v['qq'])
                ->setCellValue('I'.($k+2),$v['mobile'])
                ->setCellValue('J'.($k+2),$v['mobile_validated'])
                ->setCellValue('K'.($k+2),$v['email_validated'])
                ->setCellValue('L'.($k+2),$v['type_name'])
                ->setCellValue('M'.($k+2),$v['status'])
            ;
        }

        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，‘Excel2007’表示生成2007版本的xlsx，

        //ob_end_clean();
        header('Content-Disposition: attachment;filename="用户列表.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }

}
