<?php
/**
 * +----------------------------------------------------------------------
 * | 基础控制器
 * +----------------------------------------------------------------------
 */
namespace app\home\controller;
use think\Controller;

class Base extends Controller
{
    protected $current_action;
    public function initialize()
    {
    }

    //空操作
    public function _empty(){
        return $this->error('空操作，返回上次访问页面中...');
    }


}
