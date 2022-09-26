<?php
/**
 * +----------------------------------------------------------------------
 * | 路由部分
 * +----------------------------------------------------------------------
 */

//前台路由部分
$cate = Db::name('cate')
    ->alias('a')
    ->leftJoin('module m','a.moduleid = m.id')
    ->field('a.id,a.catname,a.catdir,m.title as modulename,m.name as moduleurl')
    ->order('a.sort ASC,a.id ASC')
    ->select();
$home_rote=[];


foreach ($cate as $k=>$v){
    //只有设置了栏目目录的栏目才配置路由
    if($v['catdir']){
        if($v['moduleurl']=='page'){
            //单页模型
            //PC
            $home_rote[''.$v['catdir'].'-:catId'] = 'home/'.$v['catdir'].'/index';
            //Mobile
            $home_rote['mobile/'.$v['catdir'].'-:catId'] = 'mobile/'.$v['catdir'].'/index';
        }else{
            //列表+详情模型
            //PC
            $home_rote[''.$v['catdir'].'-:catId/:id'] = 'home/'.$v['catdir'].'/info';
            $home_rote[''.$v['catdir'].'-:catId'] = 'home/'.$v['catdir'].'/index';
            //Mobile
            $home_rote['mobile/'.$v['catdir'].'-:catId/:id'] = 'mobile/'.$v['catdir'].'/info';
            $home_rote['mobile/'.$v['catdir'].'-:catId'] = 'mobile/'.$v['catdir'].'/index';
        }
    }
}
return $home_rote;

