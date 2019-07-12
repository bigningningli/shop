<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/24 0024
 * Time: 11:26
 */

namespace app\admin\controller;

use app\admin\model\AuthGroup;
use app\admin\model\AuthMember;
use app\admin\controller\Common;
use think\Request;
use think\Session;
use think\View;
use think\Db;
use app\admin\model\Website;
use app\admin\validate\AuthMember as membervalidate;
use app\admin\model\AuthGroupAccess;
use think\Cookie;
class System extends Common
{

    public function init(){

    }
    public function index(){
        if(!$this->auth->check('system/see',Cookie::get('uid'))){

          return view('./error/authority');
        }
        $result=$this->modelwebsite->select()->toArray();

        $this->assign([
            "result"=>$result,
            "sum"=>count($result),

        ]);
        return view();
    }

    public function system_sqlbase(){

        if(request()->isPost()){

            $this->wconfig(input('post.'),APP_PATH.'database.php',"database");


            $this->success("数据库配置成功！即将进入网站基本配置界面",'system_base');
        }
        return view();
    }
    public function system_base(){

        if(request()->isPost()){
                $memberv = new membervalidate();
                $result = $memberv->check(
                    [
                        "username" => input('adminuser'),
                        "userpassword" => input('adminpassword'),
                        "userpassword_confirm" => input('adminpassword_confirm')
                    ]
                );
            if($result) {
                if (!$this->modelmember->save(
                    [
                        "username" => input('adminuser'),
                        "userpassword" => md5(input('adminpassword')),
                    ]
                )) {
                    $this->error($this->modelmember->getError());
                };
            }else{
                $this->error($memberv->getError());
            }
            if($this->modelgroup->allowField(true)->validate(true)->save([
                "title"=>'超级管理员',
                "description"=>'超级管理员，拥有至高无上的权利',
                 "status"=>1,
                 "rules"=>"1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,l6,17,18,19,20,21,22,23"
                ])===false){
                $this->error($this->error());
            }
            if($this->modelwebsite->allowField(true)->validate(true)->save(
                [
                    "title"=>input('title'),
                    "description"=>input('description'),
                    "uploadfile"=>input('uploadfile')==""?"uploadfile":input("uploadfile"),
                    "copyright"=>input('copyright'),
                    "icp"=>input('icp'),
                    "umax"=>input('umax'),
                    "amax"=>input('amax')
                ]
            )){
            }else{
                $this->error($this->modelwebsite->getError());
            };
            $dir=explode('..',APP_PATH)[0].'static/admin/lib/ueditor/1.4.3/php/config.json';
            $temp=substr($_SERVER["SCRIPT_NAME"],0,strrpos($_SERVER["SCRIPT_NAME"],'/')).'/'.input('uploadfile');
            $this->wconfig($temp,$dir,"ueditor");
            $temp=$_SERVER["DOCUMENT_ROOT"].$temp;
            $dir=explode('..',APP_PATH)[0]."static/admin/lib/webuploader/0.1.5/server/fileupload.php";
            $this->wconfig($temp,$dir,"webuploader");
            if(!$this->modelaccess->save(
                [
                    'uid'=>$this->modelmember->id,
                    'group_id'=>$this->modelgroup->id
                ]
            )){
                $this->error($this->modelaccess->getError());
            };
            $this->success("配置成功！",'./index');
        }
        return view();
    }
    public function status(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get("uid"))){
            return json(["msg"=>"权限不足","status"=>403]);
        }
    if(request()->isPost()){
        if(!$this->modelwebsite->where("id",input('id'))->update(["status"=>1])){
            return json_encode(["msg"=>$this->modelwebsite->getError(),"status"=>500]);
        }
        if(!$this->modelwebsite->where("id",'neq',input('id'))->update(["status"=>0])){
            return json_encode(["msg"=>$this->modelwebsite->getError(),"status"=>500]);
        }
        $dir=explode('..',APP_PATH)[0].'/static/admin/lib/ueditor/1.4.3/php/config.json';
        $temp=substr($_SERVER["SCRIPT_NAME"],0,strrpos($_SERVER["SCRIPT_NAME"],'/')).'/'.$this->modelwebsite->where("id",input('id'))->select()->toArray()[0]["uploadfile"];
        $this->wconfig($temp,$dir,"ueditor");
        $temp=$_SERVER["DOCUMENT_ROOT"].$temp;
        $dir=explode('..',APP_PATH)[0]."/static/admin/lib/webuploader/0.1.5/server/fileupload.php";
        $this->wconfig($temp,$dir,"webuploader");

        return json_encode(["msg"=>"已启用","status"=>200]);
    }
    }
    public function edit(){
        if(!$this->auth->check(request()->controller().'/edit',Cookie::get('uid'))){
            return view('./error/authority');
        }
        $result=$this->modelwebsite->where("id",input('id'))->select()->toArray()[0];

        if(!$result){
            return json_encode(["msg"=>"未查找到结果或数据库配置错误","status"=>404]);
        }
        $id=["id"=>input('id')];
        $data=input('post.');
        if(request()->isPost()) {

            if($this->modelwebsite->where('id',input('id'))->select()->toArray()[0]["status"]==1){
                    $dir=explode('..',APP_PATH)[0].'/static/admin/lib/ueditor/1.4.3/php/config.json';
                    $temp=$_SERVER["DOCUMENT_ROOT"].substr($_SERVER["SCRIPT_NAME"],0,strrpos($_SERVER["SCRIPT_NAME"],'/')).'/'.input('uploadfile');
                    $this->wconfig($temp,$dir,"ueditor");
                    $dir=explode('..',APP_PATH)[0]."/static/admin/lib/webuploader/0.1.5/server/fileupload.php";
                    $this->wconfig($temp,$dir,"webuploader");
            }

            if ($this->modelwebsite->allowField(true)->save(
                $data,$id
            )) {
              return json_encode(["msg"=>"修改成功","status"=>200]);
            }else{
              return json_encode(["msg"=>$this->modelwebsite->getError(),"status"=>500]);
            }
        }


        $this->assign([
            "result"=>$result,
            "webuploader"=>$this->webuploader
        ]);
        return view();
    }
    public function logodel(){
        if(!$this->auth->check(request()->controller().'/delete',Cookie::get("uid"))){
            return json(["msg"=>"权限不足","status"=>403]);
        }
        if(request()->isPost()){
            $this->modelwebsite->startTrans();
            if($this->modelwebsite->where('id',input('id'))->setField('logo','')){
                if(unlink($_SERVER["CONTEXT_DOCUMENT_ROOT"].input('dir'))){
                    $this->modelwebsite->commit();

                    return json_encode(["msg"=>"删除成功","status"=>200]);

                }else{
                    $this->modelwebsite->rollback();

                    return json_encode(["msg"=>"删除失败","status"=>500]);
                }
            }else{
                $this->modelwebsite->rollback();

                return json_encode(["msg"=>$this->modelwebsite->getError(),"status"=>500]);
            }
        }
    }
    public function delete(){

        if(request()->isPost()) {
            if (count($this->modelwebsite->where("id in (" . input('id') . ")")->where('status', 1)->select()->toArray()) != 0) {
                return json_encode(["msg" => "使用中的配置文件不能删除！", "status" => 500]);
            }
            $this->modelwebsite->startTrans();

            $arr=$this->modelwebsite->where("id in (" . input('id') . ")")->column('logo');

            foreach($arr as $value){
                if (!unlink($_SERVER["CONTEXT_DOCUMENT_ROOT"].$this->webuploader . $value)) {
                    $this->modelwebsite->rollback();
                    return json_encode(["msg" => "删除失败", "status" => 500]);
                }
            }
            if ($this->modelwebsite->where("id in (" . input('id') . ")")->delete()) {

                $this->modelwebsite->commit();
                return json_encode(["msg" => "删除成功", "status" => 200]);
            } else {
                $this->modelwebsite->rollback();
                return json_encode(["msg" => $this->modeadvertisement->getError(), "status" => 500]);
            }
        }

    }
    public function add(){
        if(!$this->auth->check(request()->controller().'/add',Cookie::get('uid'))){
            return view('./error/authority');
        }
        if(request()->isPost()){

            $data=input('post.');
            foreach ($data as $key=>$val){
                if($val==""){
                    unset($data[$key]);
                }
            }
            if($this->modelwebsite->allowField(true)->validate(true)->save(
                $data
            )){
                return json_encode(["msg"=>"添加成功","status"=>200]);
            }else{
                return json_encode(["msg"=>$this->modelwebsite->getError(),"status"=>500]);
            }

        }
        return view();
    }
    public function system_category(){
        return view();
    }

//    @public
//    @配置文件状态更改
//    @return json([提示信息，状态])

    public function website_status(){

    }

//    @public
//    @部分配置文件修改写入，方法
//    @$temp修改为的变量,$dir目录,$type要修改的类型
//    @return 写入成功与否的状态APP_PATH.'database.php'\

    public function wconfig($temp,$dir,$type){
        switch ($type){
            case "database":
                $f=fopen($dir,"w");
                $str="
            <?php
//+----------------------------------------------------------------------
//|ThinkPHP[WECANDOITJUSTTHINK]
//+----------------------------------------------------------------------
//|Copyright(c)2006~2018http://thinkphp.cnAllrightsreserved.
//+----------------------------------------------------------------------
//|Licensed(http://www.apache.org/licenses/LICENSE-2.0)
//+----------------------------------------------------------------------
//|Author:liu21st<liu21st@gmail.com>
//+----------------------------------------------------------------------

return[
//数据库类型
'type'=>'mysql',
//服务器地址
'hostname'=>'".$temp["dbhost"]."',
//数据库名
'database'=>'BBS',
//用户名
'username'=>'".$temp["dbuser"]."',
//密码
'password'=>'".$temp["dbpassword"]."',
//端口
'hostport'=>'".$temp["port"]."',
//连接dsn
'dsn'=>'',
//数据库连接参数
'params'=>[],
//数据库编码默认采用utf8
'charset'=>'utf8',
//数据库表前缀
'prefix'=>'think_',
//数据库调试模式
'debug'=>true,
//数据库部署方式:0集中式(单一服务器),1分布式(主从服务器)
'deploy'=>0,
//数据库读写是否分离主从式有效
'rw_separate'=>false,
//读写分离后主服务器数量
'master_num'=>1,
//指定从服务器序号
'slave_no'=>'',
//自动读取主库数据
'read_master'=>false,
//是否严格检查字段是否存在
'fields_strict'=>true,
//数据集返回类型
'resultset_type'=>'\\think\Collection',
//自动写入时间戳字段
'auto_timestamp'=>false,
//时间字段取出后的默认时间格式
'datetime_format'=>'Y-m-d H:i:s',
//是否需要进行SQL性能分析
'sql_explain'=>false,
];  ";

                fwrite($f,$str);
                fclose($f);
               break ;
            case "ueditor":  $f=fopen($dir,'w');

$str='     
{

    "imageActionName": "uploadimage",  
    "imageFieldName": "upfile",  
    "imageMaxSize": 2048000,  
    "imageAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"],  
    "imageCompressEnable": true,  
    "imageCompressBorder": 1600,  
    "imageInsertAlign": "none",  
    "imageUrlPrefix": "",  
    "imagePathFormat":"'.$temp.'/ueditor/image/{yyyy}{mm}{dd}/{time}{rand:6}",  
  
    "scrawlActionName": "uploadscrawl",  
    "scrawlFieldName": "upfile",  
    "scrawlPathFormat": "'.$temp.'/ueditor/image/{yyyy}{mm}{dd}/{time}{rand:6}",  
    "scrawlMaxSize": 2048000,  
    "scrawlUrlPrefix": "",  
    "scrawlInsertAlign": "none",

     
    "snapscreenActionName": "uploadimage",  
    "snapscreenPathFormat": "'.$temp.'/ueditor/image/{yyyy}{mm}{dd}/{time}{rand:6}",  
    "snapscreenUrlPrefix": "",  
    "snapscreenInsertAlign": "none",  

     
    "catcherLocalDomain": ["127.0.0.1", "localhost", "img.baidu.com"],
    "catcherActionName": "catchimage",  
    "catcherFieldName": "source",  
    "catcherPathFormat": "'.$temp.'/ueditor/image/{yyyy}{mm}{dd}/{time}{rand:6}",  
    "catcherUrlPrefix": "",  
    "catcherMaxSize": 2048000,  
    "catcherAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"],  

    "videoActionName": "uploadvideo",  
    "videoFieldName": "upfile",  
    "videoPathFormat": "'.$temp.'/ueditor/video/{yyyy}{mm}{dd}/{time}{rand:6}",  
    "videoUrlPrefix": "",  
    "videoMaxSize": 102400000,  
    "videoAllowFiles": [
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"],  

    "fileActionName": "uploadfile",  
    "fileFieldName": "upfile",  
    "filePathFormat": "'.$temp.'/ueditor/file/{yyyy}{mm}{dd}/{time}{rand:6}",  
    "fileUrlPrefix": "",  
    "fileMaxSize": 51200000,  
    "fileAllowFiles": [
        ".png", ".jpg", ".jpeg", ".gif", ".bmp",
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
        ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
        ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
    ],
    "imageManagerActionName": "listimage",  
    "imageManagerListPath": "'.$temp.'/ueditor/image/",  
    "imageManagerListSize": 20,  
    "imageManagerUrlPrefix": "",  
    "imageManagerInsertAlign": "none",  
    "imageManagerAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"],
    "fileManagerActionName": "listfile",  
    "fileManagerListPath": "'.$temp.'/ueditor/file/",  
    "fileManagerUrlPrefix": "",  
    "fileManagerListSize": 20,  
    "fileManagerAllowFiles": [
        ".png", ".jpg", ".jpeg", ".gif", ".bmp",
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
        ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
        ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
    ]  

} ';
          fwrite($f,$str);
          fclose($f);
          break;
     case "webuploader": $f=fopen($dir,'w');

$str= '<?php
/**
 * upload.php
 *
 * Copyright 2013, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

#!! 注意
#!! 此文件只是个示例，不要用于真正的产品之中。
#!! 不保证代码安全性。

#!! IMPORTANT:
#!! this file is just an example, it doesn\'t incorporate any security checks and
#!! is not recommended to be used in production environment as it is. Be sure to
#!! revise it and customize to your needs.


// Make sure file is not cached (as it happens for example on iOS devices)
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Support CORS
// header("Access-Control-Allow-Origin: *");
// other CORS headers if any...
if ($_SERVER[\'REQUEST_METHOD\'] == \'OPTIONS\') {
    exit; // finish preflight CORS requests here
}


if ( !empty($_REQUEST[ \'debug\' ]) ) {
    $random = rand(0, intval($_REQUEST[ \'debug\' ]) );
    if ( $random === 0 ) {
        header("HTTP/1.0 500 Internal Server Error");
        exit;
    }
}

// header("HTTP/1.0 500 Internal Server Error");
// exit;


// 5 minutes execution time
@set_time_limit(5 * 60);

// Uncomment this one to fake upload time
// usleep(5000);

// Settings
// $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
$targetDir = \'upload_tmp\';
$uploadDir ="'.$temp.'/webuploader/images";

$cleanupTargetDir = true; // Remove old files
$maxFileAge = 5 * 3600; // Temp file age in seconds


// Create target dir
if (!file_exists($targetDir)) {
    @mkdir($targetDir);
}

// Create target dir
if (!file_exists($uploadDir)) {
    @mkdir($uploadDir);
}

// Get a file name
if (isset($_REQUEST["name"])) {
    $fileName = rand(0,1000).$_REQUEST["name"];
} elseif (!empty($_FILES)) {
    $fileName = rand(0,1000).$_FILES["file"]["name"];
} else {
    $fileName = rand(0,1000).uniqid("file_");
}

$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
$uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

// Chunking might be enabled
$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;


// Remove old temp files
if ($cleanupTargetDir) {
    if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
        die(\'{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}\');
    }

    while (($file = readdir($dir)) !== false) {
        $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

        // If temp file is current file proceed to the next
        if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
            continue;
        }

        // Remove temp file if it is older than the max age and is not the current file
        if (preg_match(\'/\.(part|parttmp)$/\', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
            @unlink($tmpfilePath);
        }
    }
    closedir($dir);
}


// Open temp file
if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
    die(\'{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}\');
}

if (!empty($_FILES)) {
    if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
        die(\'{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}\');
    }

    // Read binary input stream and append it to temp file
    if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
        die(\'{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}\');
    }
} else {
    if (!$in = @fopen("php://input", "rb")) {
        die(\'{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}\');
    }
}

while ($buff = fread($in, 4096)) {
    fwrite($out, $buff);
}

@fclose($out);
@fclose($in);

rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

$index = 0;
$done = true;
for( $index = 0; $index < $chunks; $index++ ) {
    if ( !file_exists("{$filePath}_{$index}.part") ) {
        $done = false;
        break;
    }
}
if ( $done ) {
    if (!$out = @fopen($uploadPath, "wb")) {
        die(\'{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}\');
    }

    if ( flock($out, LOCK_EX) ) {
        for( $index = 0; $index < $chunks; $index++ ) {
            if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                break;
            }

            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);
            }

            @fclose($in);
            @unlink("{$filePath}_{$index}.part");
        }

        flock($out, LOCK_UN);
    }
    @fclose($out);
}

// Return Success JSON-RPC response
die(\'{"jsonrpc" : "2.0", "result" :"\'.$fileName.\'", "id" : "id"}\');
';
                fwrite($f,$str);
                fclose($f);
                break;
        }
    }


}