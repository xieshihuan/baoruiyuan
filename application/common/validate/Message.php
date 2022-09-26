<?php
/**
 * +----------------------------------------------------------------------
 * | 栏目验证器
 * +----------------------------------------------------------------------
 */
namespace app\common\validate;

use think\Validate;

class Message extends Validate
{
    protected $rule = [
        'name|名称' => [
            'require' => 'require',
            'max'     => '255',
        ],
        'phone|手机号' => [
            'require' => 'require',
            'number'  => 'number',
        ],
        'content|内容' => [
            'require' => 'require',
            'max'     => '255',
        ]
    ];
}