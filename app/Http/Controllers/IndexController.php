<?php

namespace App\Http\Controllers;
use DB,Session,Validator;
use Illuminate\Http\Request;
use App\Http\Requests;

class IndexController extends Controller
{
    //注册
    public function register(Request $request){
        $data=$request->all();
        $user_name=$data['user_name'];
        $user_pwd=$data['user_pwd'];
        $user_email=$data['user_email'];
        $user_phone=$data['user_phone'];
        //用户名非空
        if(!empty($user_name) && !empty($user_pwd) && !empty($user_email) && !empty($user_phone)){
            //验证用户名唯一性
            $arr = DB::table('users')->where('user_name',$user_name)->first();
            if($arr){
                //用户名已占用
                $msg=array(
                    "data"=>'失败',
                    "info"=>'用户名已被占用',
                    "error"=>'1005'
                );
                return json_encode($msg);
            }else if(!preg_match("/^[\w-\.]{6,16}$/",$user_pwd)){
                //密码格式正确
                $msg=array(
                    "data"=>'密码错误',
                    "info"=>'密码格式不正确(6-16位)',
                    "error"=>'1007'
                );
                return json_encode($msg);
            }else if(!preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i",$user_email)){
                $msg=array(
                    "data"=>'邮箱错误',
                    "info"=>'邮箱格式不正确',
                    "error"=>'1008'
                );
                return json_encode($msg);
            }else if(!preg_match("/^1[34578]{1}\d{9}$/",$user_phone)){

                $msg=array(
                    "data"=>'手机号错误',
                    "info"=>'手机号格式不正确',
                    "error"=>'1009'
                );
                return json_encode($msg);
            }else{
                $validator = Validator::make($data, [
                    'user_name' => 'required|between:4,32|unique:users',
                    'user_pwd' => 'required|between:6,16',
                    'user_email'=>'required|unique:users',
                    'user_phone'=>'required|unique:users'
                ]);
                if($validator->errors()->all())
                {
                    //获取用户注册错误信息
                    $error = $validator->errors()->all();

                    $english = $this->Error($error);
                    //调用英文翻译接口
                    $url = "http://fanyi.youdao.com/openapi.do?keyfrom=qwe1123&key=710353888&type=data&doctype=json&version=1.1&q=".$english;

                    //将内容读取出来
                    $file = file_get_contents($url);
                    $wrong=json_decode($file,true);
                    $translation=$wrong['translation'];
                    $msg=array(
                        "data"=>$translation,
                        "info"=>$translation,
                        "error"=>'1010'
                    );
                    return json_encode($msg);
                }else{
                    /*  注册
                     * //将数据入库
                     */
                    $name = $data['user_name'];
                    $pwd = md5(md5($data['user_pwd']));
                    $email = $data['user_email'];
                    $phone = $data['user_phone'];
                    $arr=DB::insert("insert into users(user_name,user_pwd,user_email,user_phone) values('$name','$pwd','$email','$phone')");
                    //设置用户session值
                    if($arr){
                        //添加成功
                        $dataid = DB::table("users")->where("user_name",$name)->select("user_id")->first();
                        $msg=array(
                            'user_id'=>$dataid['user_id'],
                            "data"=>"成功",
                            "info"=>"注册成功",
                            "error"=>'1000'
                        );
                        return json_encode($msg);
                    }
                }
            }
        }else{
            //参数有误
            $msg=array(
                "data"=>'失败',
                "info"=>'参数有误',
                "error"=>'1006'
            );
            return json_encode($msg);
        }
    }
    //登录接口
    public function login(Request $request){
        $logindata=$request->all();
        $user_name=isset($logindata['user_name'])?$logindata['user_name']:"";
        $pwd=isset($logindata['user_pwd'])?$logindata['user_pwd']:"";
        $user_pwd=md5(md5($pwd));
        if(!empty($user_name) && !empty($user_pwd)){
            if(preg_match("/^1[34578]{1}\d{9}$/",$user_name)){
                //是手机号  手机号和密码验证
                $arr = DB::table('users')
                    ->where('user_phone', $user_name)
                    ->where('user_pwd', $user_pwd)
                    ->first();
                if($arr){
                    $msg=array(
                        'user_id'=>$arr['user_id'],
                        "data"=>'成功',
                        "info"=>'手机号和密码正确',
                        "error"=>"1000"
                    );
                    return json_encode($msg);
                }else{
                    $msg=array(
                        "data"=>'失败',
                        "info"=>'手机号或密码输入有误',
                        "error"=>"1001"
                    );
                    return json_encode($msg);
                }
            }else if(preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i",$user_name)){
                //验证邮箱和密码是否匹配
                $arr = DB::table('users')
                    ->where('user_email', $user_name)
                    ->where('user_pwd', $user_pwd)
                    ->first();
                if($arr){
                    $msg=array(
                        'user_id'=>$arr['user_id'],
                        "data"=>'成功',
                        "info"=>'邮箱和密码输入正确',
                        "error"=>"1000"
                    );
                    return json_encode($msg);
                }else{
                    $msg=array(
                        "data"=>'失败',
                        "info"=>'邮箱或密码输入有误',
                        "error"=>"1002"
                    );
                    return json_encode($msg);
                }
            }else{
                $msg=array(
                    "data"=>'有误',
                    "info"=>'输入有误',
                    "error"=>"1003"
                );
                return json_encode($msg);
            }
        }else{
            $msg=array(
                "data"=>'失败',
                "info"=>'用户名和密码不能为空',
                "error"=>"1004"
            );
            return json_encode($msg);
        }
    }
}
