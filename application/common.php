<?php
/**
 * +----------------------------------------------------------------------
 * | 应用公共文件
 * +----------------------------------------------------------------------
 */

// 定义插件目录
define('ADDON_PATH', Env::get('root_path') . 'addons' . DIRECTORY_SEPARATOR);

// 闭包自动处理插件钩子业务
Hook::add('app_init', function () {
    // 获取开关
    $autoload = (bool)Config::get('addons.autoload', false);
    // 配置自动加载时直接返回
    if ($autoload) return;
    // 非正时表示后台接管插件业务
    // 当debug时不缓存配置
    $config = config('app_debug') ? [] : (array)cache('addons');
    if (empty($config)) {
        //读取插件通过文件夹的形式来读取
        $hooks = get_addon_list();
        foreach ($hooks as $hook) {
            //是否开启该插件,只有开启的插件才加载
            if($hook['status']==1)
                $config['hooks'][$hook['name']] = explode(',', $hook['addons']);
        }
        cache('addons', $config);
    }
    config('addons', $config);
});

/**
 * 过滤数组元素前后空格 (支持多维数组)
 * @param $array 要过滤的数组
 * @return array|string
 */
function trim_array_element($array){
    if(!is_array($array))
        return trim($array);
    return array_map('trim_array_element',$array);
}

/**
 * 将数据库中查出的列表以指定的 值作为数组的键名，并以另一个值作为键值
 * @param $arr
 * @param $key_name
 * @return array
 */
function convert_arr_kv($arr,$key_name,$value){
    $arr2 = array();
    foreach($arr as $key => $val){
        $arr2[$val[$key_name]] = $val[$value];
    }
    return $arr2;
}

/**
 * 验证输入的邮件地址是否合法
 */
function is_email($user_email)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
        if (preg_match($chars, $user_email)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 验证输入的手机号码是否合法
 */
function is_mobile_phone($mobile_phone)
{
    $chars = "/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/";
    if (preg_match($chars, $mobile_phone)) {
        return true;
    }
    return false;
}

/**
 * 邮件发送
 * @param $to    接收人
 * @param string $subject   邮件标题
 * @param string $content   邮件内容(html模板渲染后的内容)
 * @throws Exception
 * @throws phpmailerException
 */
function send_email($to,$subject='',$content=''){
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $arr = Db::name('config')->where('inc_type','smtp')->select();
    $config = convert_arr_kv($arr,'name','value');

    $mail->CharSet  = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    //调试输出格式
    //$mail->Debugoutput = 'html';
    //smtp服务器
    $mail->Host = $config['smtp_server'];
    //端口 - likely to be 25, 465 or 587
    $mail->Port = $config['smtp_port'];

    if($mail->Port == '465') {
        $mail->SMTPSecure = 'ssl';
    }// 使用安全协议
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //发送邮箱
    $mail->Username = $config['smtp_user'];
    //密码
    $mail->Password = $config['smtp_pwd'];
    //Set who the message is to be sent from
    $mail->setFrom($config['smtp_user'],$config['email_id']);
    //回复地址
    //$mail->addReplyTo('replyto@example.com', 'First Last');
    //接收邮件方
    if(is_array($to)){
        foreach ($to as $v){
            $mail->addAddress($v);
        }
    }else{
        $mail->addAddress($to);
    }

    $mail->isHTML(true);// send as HTML
    //标题
    $mail->Subject = $subject;
    //HTML内容转换
    $mail->msgHTML($content);
    return $mail->send();
}

function string2array($info) {
    if($info == '') return array();
    eval("\$r = $info;");
    return $r;
}
function array2string($info) {
    if($info == '') return '';
    if(!is_array($info)){
        $string = stripslashes($info);
    }
    foreach($info as $key => $val){
        $string[$key] = stripslashes($val);
    }
    $setup = var_export($string, TRUE);
    return $setup;
}
//文本域中换行标签输出
function textareaBr($info) {
    $info = str_replace("\r\n","<br />",$info);
    return $info;
}

// 无限分类-栏目
function tree_cate($cate , $lefthtml = '|— ' , $pid=0 , $lvl=0 ){
    $arr=array();
    foreach ($cate as $v){
        if($v['parentid']==$pid){
            $v['lvl']=$lvl + 1;
            $v['lefthtml']=str_repeat($lefthtml,$lvl);
            $v['lcatname']=$v['lefthtml'].$v['catname'];
            $arr[]=$v;
            $arr= array_merge($arr,tree_cate($cate,$lefthtml,$v['id'], $lvl+1 ));
        }
    }
    return $arr;
}

//组合多维数组
function unlimitedForLayer ($cate, $name = 'sub', $pid = 0) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parentid'] == $pid) {
            $v[$name] = unlimitedForLayer($cate, $name, $v['id']);
            $v['url'] = getUrl($v);
            $arr[] = $v;
        }

    }
    return $arr;
}

//传递一个父级分类ID返回当前子分类
function getChildsOn ($cate, $pid) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parentid'] == $pid) {
            $v['sub'] = getChilds($cate, $v['id']);
            $v['url'] = getUrl($v);
            $arr[] = $v;
        }
    }
    return $arr;
}

//传递一个父级分类ID返回所有子分类
function getChilds ($cate, $pid) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parentid'] == $pid) {
            $v['url'] = getUrl($v);
            $arr[] = $v;
            $arr = array_merge($arr, getChilds($cate, $v['id']));
        }
    }
    return $arr;
}

//传递一个父级分类ID返回所有子分类ID
function getChildsId ($cate, $pid) {
    $arr = [];
    foreach ($cate as $v) {
        if ($v['parentid'] == $pid) {
            $arr[] = $v;
            $arr = array_merge($arr, getChildsId($cate, $v['id']));
        }
    }
    return $arr;
}
//格式化分类数组为字符串
function getChildsIdStr($ids,$pid=''){
    $result='';
    foreach ($ids as $k=>$v){
        $result.=$v['id'].',';
    }
    if($pid){
        $result = $pid.','.$result;
    }
    $result = rtrim($result,',');
    return $result;
}

//传递一个子分类ID返回所有的父级分类
function getParents ($cate, $id) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['id'] == $id) {
            $arr[] = $v;
            $arr = array_merge(getParents($cate, $v['parentid']), $arr);
        }
    }
    return $arr;
}

//URL设置
function getUrl($v){
    //判断是否直接跳转
    if(trim($v['url'])!==''){

    }else{
        //判断是否跳转到下级栏目
        if($v['is_next']==1){
            $is_next = Db::name('cate')->where('parentid',$v['id'])->order('sort ASC,id DESC')->find();
            if($is_next){
                $v['url'] = getUrl($is_next);
            }
        }else{
            $moduleurl = Db::name('module')->where('id',$v['moduleid'])->value('name');
            if($v['catdir']){
                $v['url'] = url(request()->module().'/'.$v['catdir'].'/index', 'catId='.$v['id']);
            }else{
                $v['url'] = url(request()->module().'/'.$moduleurl.'/index', 'catId='.$v['id']);
            }
        }
    }
    return $v['url'];
}

//获取详情URL
function getShowUrl($v){
    if($v){
        //$home_rote[''.$v['catdir'].'-:catId/:id'] = 'home/'.$v['catdir'].'/index';
        $cate = Db::name('cate')->field('id,catdir,moduleid')->where('id',$v['catid'])->find();
        $moduleurl = Db::name('module')->where('id',$cate['moduleid'])->value('name');
        if($cate['catdir']){
            $url = url(request()->module().'/'.$cate['catdir'].'/info', ['catId'=>$cate['id'],'id'=>$v['id']]);
        }else{
            $url = url(request()->module().'/'.$moduleurl.'/info', ['catId'=>$cate['id'],'id'=>$v['id']] );
        }
    }
    return $url;
}

//获取所有模版
function getTemplate(){
    //查找设置的模版
    $system = Db::name('system')->where('id',1)->find();
    $path = './template/home/'.$system['template'].'/'.$system['html'].'/';
    $tpl['list'] = get_file_folder_List($path , 2, '*_list*');
    $tpl['show'] = get_file_folder_List($path , 2, '*_show*');
    return $tpl;
}

/**
 * 获取文件目录列表
 * @param string $pathname 路径
 * @param integer $fileFlag 文件列表 0所有文件列表,1只读文件夹,2是只读文件(不包含文件夹)
 * @param string $pathname 路径
 * @return array
 */
function get_file_folder_List($pathname,$fileFlag = 0, $pattern='*') {
    $fileArray = array();
    $pathname = rtrim($pathname,'/') . '/';
    $list   =   glob($pathname.$pattern);
    foreach ($list  as $i => $file) {
        switch ($fileFlag) {
            case 0:
                $fileArray[]=basename($file);
                break;
            case 1:
                if (is_dir($file)) {
                    $fileArray[]=basename($file);
                }
                break;

            case 2:
                if (is_file($file)) {
                    $fileArray[]=basename($file);
                }
                break;

            default:
                break;
        }
    }

    return $fileArray;
}

function changeFields($list,$moduleid){
    $info = [];
    foreach ($list as $k=>$v){
        $url = getShowUrl($v);
        $list[$k] = changeField($v,$moduleid);
        $info[$k] = $list[$k];//定义中间变量防止报错
        $info[$k]['url'] = $url;
    }
    return $info;
}
function changefield($info,$moduleid){
    $fields = Db::name('field')->where('moduleid','=',$moduleid)->select();
    foreach ($fields as $k=>$v){
        $field = $v['field'];
        if($info[$field]){
            switch ($v['type'])
            {
                case 'textarea'://多行文本
                    break;
                case 'editor'://编辑器
                    $info[$field]=($info[$field]);
                    break;
                case 'select'://下拉列表
                    break;
                case 'radio'://单选按钮
                    break;
                case 'checkbox'://复选框
                    $info[$field]=explode(',',$info[$field]);
                    break;
                case 'images'://多张图片
                    $info[$field]=json_decode($info[$field],true);
                    break;
                default:
            }
        }

    }

    return $info;
}

/**
 * 判断当前访问的用户是  PC端  还是 手机端  返回true 为手机端  false 为PC 端
 *  是否移动端访问访问
 * @return boolean
 */
function isMobile()
{
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            return true;
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
    return false;
}

/**
 * 获得本地插件列表
 * @return array
 */
function get_addon_list()
{
    $results = scandir(ADDON_PATH);
    $list = [];
    foreach ($results as $name) {
        if ($name === '.' or $name === '..')
            continue;
        if (is_file(ADDON_PATH . $name))
            continue;
        $addonDir = ADDON_PATH . $name . DIRECTORY_SEPARATOR;
        if (!is_dir($addonDir))
            continue;

        if (!is_file($addonDir . ucfirst($name) . '.php'))
            continue;

        //这里不采用get_addon_info是因为会有缓存
        //$info = get_addon_info($name);
        $info_file = $addonDir . 'info.ini';
        if (!is_file($info_file))
            continue;

        $info = Config::parse($info_file, '', "addon-info-{$name}");
        //$info['url'] = addon_url($name);
        $list[$name] = $info;
    }
    return $list;
}

/**
 * 判断文件或文件夹是否可写
 * @param    string $file 文件或目录
 * @return    bool
 */
function is_really_writable($file)
{
    if (DIRECTORY_SEPARATOR === '/') {
        return is_writable($file);
    }
    if (is_dir($file)) {
        $file = rtrim($file, '/') . '/' . md5(mt_rand());
        if (($fp = @fopen($file, 'ab')) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        @chmod($file, 0777);
        @unlink($file);
        return TRUE;
    } elseif (!is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE) {
        return FALSE;
    }
    fclose($fp);
    return TRUE;
}

/**
 * 插件更新配置文件
 *
 * @param string $name 插件名
 * @param array $array
 * @return boolean
 * @throws Exception
 */
function set_addon_fullconfig($name, $array)
{
    $file = ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'config.php';
    if (!is_really_writable($file)) {
        throw new Exception("文件没有写入权限");
    }
    if ($handle = fopen($file, 'w')) {
        fwrite($handle, "<?php\n\n" . "return " . var_export($array, TRUE) . ";\n");
        fclose($handle);
    } else {
        throw new Exception("文件没有写入权限");
    }
    return true;
}
/**
 * 插件更新ini文件
 *
 * @param string $name 插件名
 * @param array $array
 * @return boolean
 * @throws Exception
 */
function set_addon_fullini($name, $array)
{
    $file = ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'info.ini';
    if (!is_really_writable($file)) {
        throw new Exception("文件没有写入权限");
    }
    $str = '';
    foreach($array as $k=>$v){
        $str .= $k." = ".$v."\n";
    }

    if ($handle = fopen($file, 'w')) {
        fwrite($handle, $str);
        fclose($handle);
    } else {
        throw new Exception("文件没有写入权限");
    }
    return true;
}

function xfyun(){
    $daytime=strtotime('1970-1-1T00:00:00 UTC');
	// OCR手写文字识别服务webapi接口地址
    $api = "http://webapi.xfyun.cn/v1/service/v1/ocr/handwriting";
	// 应用APPID(必须为webapi类型应用,并开通手写文字识别服务,参考帖子如何创建一个webapi应用：http://bbs.xfyun.cn/forum.php?mod=viewthread&tid=36481)
    $XAppid = "89463718";
	// 接口密钥(webapi类型应用开通手写文字识别后，控制台--我的应用---手写文字识别---相应服务的apikey)
    $Apikey = "455082566c6c70c03a6427d0966624eb";
    $XCurTime =time();
    $XParam ="";
    $XCheckSum ="";
    // 语种设置和是否返回文本位置信息
    $Param= array(
		"language"=>"cn|en",
		"location"=>"false",
    );
	// 文件上传地址
    $image=file_get_contents('../public/1.jpeg');
    $image=base64_encode($image);		    
    $Post = array(
	  'image' => $image,
	);
    $XParam = base64_encode(json_encode($Param));
    $XCheckSum = md5($Apikey.$XCurTime.$XParam);
    $headers = array();
    $headers[] = 'X-CurTime:'.$XCurTime;
    $headers[] = 'X-Param:'.$XParam;
    $headers[] = 'X-Appid:'.$XAppid;
    $headers[] = 'X-CheckSum:'.$XCheckSum;
    $headers[] = 'Content-Type:application/x-www-form-urlencoded; charset=utf-8';
    return http_request($api, $Post, $headers);
}
/**
 * 发送post请求
 * @param string $url 请求地址
 * @param array $post_data post键值对数据
 * @return string
 */
function http_request($url, $post_data, $headers) {		 
  $postdata = http_build_query($post_data);
  $options = array(
    'http' => array(
      'method' => 'POST',
      'header' => $headers,
      'content' => $postdata,
      'timeout' => 15 * 60 // 超时时间（单位:s）
    )
  );
  $context = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
// 错误码链接：https://www.xfyun.cn/document/error-code (code返回错误码时必看)	
  echo $result; 			
  return "success";
}



function curlGet($url){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url); 

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
}


function sortArrByManyField() {
    $args = func_get_args(); // 获取函数的参数的数组

    if (empty($args)) {
        return null;
    } 
    $arr = array_shift($args); // array_shift() 函数删除数组中第一个元素，并返回被删除元素的值
    if (!is_array($arr)) {
        throw new Exception("第一个参数不为数组");
    } 
    foreach($args as $key => $field) {
        if (is_string($field)) {
            $temp = array();
            foreach($arr as $index => $val) {
                $temp[$index] = $val[$field];
            } 
            $args[$key] = $temp;
        } 
    } 
    $args[] = &$arr; //引用值
    call_user_func_array('array_multisort', $args);
    return array_pop($args); // array_pop()功能是数组的最后一个元素出栈，返回值是数组的最后一个元素
} 


    
/**
 * 获取指定日期段内的每一天日期
 *
 * @param $start_date // 1615963603 || 2021-03-10
 * @param $end_date   // 1615963603 || 2021-03-10
 * @return array
 */
function getDateFromRange($start_date, $end_date)
{
    $date = [];
    $stimestamp = is_numeric($start_date)?$start_date:strtotime($start_date);
    $etimestamp = is_numeric($end_date)?$end_date:strtotime($end_date);

    // 计算日期段内有多少天
    $days = ( $etimestamp - $stimestamp ) / 86400;
    for( $i=0; $i<$days; $i++ ){
        $date[] = date('Y-m-d', $stimestamp + ( 86400 * $i ) );
    }

    return $date;
}
