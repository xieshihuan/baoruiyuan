<?php
/**
 * +----------------------------------------------------------------------
 * | 栏目验证器
 * +----------------------------------------------------------------------
 */
namespace app\common\validate;

use think\Validate;

class Cate extends Validate
{
    protected $rule = [
        'catname|栏目名称' => [
            'require' => 'require',
            'max'     => '100',
        ],
        'parentid|栏目上级' => [
            'require' => 'require',
            'number'  => 'number',
        ],
        'summary|简介' => [
            'max' => '800',
        ],
        'sort|排序' => [
            'require' => 'require',
            'number'  => 'number',
        ]
    ];
}