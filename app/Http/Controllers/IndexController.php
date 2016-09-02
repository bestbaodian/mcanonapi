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

//                    $english = $this->Error($error);
                    //调用英文翻译接口
//                    $url = "http://fanyi.youdao.com/openapi.do?keyfrom=qwe1123&key=710353888&type=data&doctype=json&version=1.1&q=".$english;

                    //将内容读取出来
//                    $file = file_get_contents($url);
//                    $wrong=json_decode($file,true);
//                    $translation=$wrong['translation'];
                    $msg=array(
                        "data"=>$error,
                        "info"=>$error,
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
    /*
    *修改密码
        */
    public function updpwd(Request $request)
    {
        //用户id
        $uid=$request->get('uid');
        //用户旧密码
        $oldpwd=$request->get('oldpwd');
        //用户新密码
        $newpwds=$request->get('newpwd');
        //密码加密
        $newpwd=md5(md5($newpwds));
        //判断是否为空
        if(empty($uid)||empty($oldpwd)||empty($newpwd)){

            $result["error"]=1;
            $result["msg"]="参数有误";
        }
        else{
            $opwd = md5(md5($oldpwd));
            $arr=DB::table("users")->where("user_id","$uid")->where("user_pwd","$opwd")->get();
            if($arr)
            {
                //根据用户id修改密码
                $upd=DB::table("users")->where("user_id","$uid")->update(["user_pwd"=>"$newpwd"]);
                if($upd)
                {
                    $result["error"]=0;
                    $result["msg"]="修改成功";
                }
                else
                {
                    $result["error"]=2;
                    $result["msg"]="修改失败";
                }
            }
            else
            {
                $result["error"]=1;
                $result["msg"]="参数有误";
            }
        }
        return json_encode($result);
    }

    //面试资料
    public function IC_show(Request $request){
        $user_id = $request->get("user_id");
        if(!empty($user_id)){
            $arr  =DB::table('ic')->select('*')
                ->join("users","ic.u_id","=","users.user_id")
                ->join("career","career.c_id","=","users.user_job")
                ->select("company","time","user_name","c_career","ic.company_address","describe")
                ->where('u_id',$user_id)
                ->orderBy('time','desc')
                ->paginate(10);
            $data['error']=0;
            $data['user']=$arr;
            return json_encode($data);
        }else{
            $msg=array(
                "data"=>'user_id有误',
                "info"=>'用户id参数不正确',
                "error"=>"2001"
            );
            return json_encode($msg);
        }
    }
    //其他用户面试资料展示
    public function other_show(){
        $ic=DB::table('ic')
            ->leftjoin('userinfo','ic.u_id','=','userinfo.u_id')
            ->join("users","userinfo.u_id","=","users.user_id")
            ->join("career","career.c_id","=","users.user_job")
            ->select('userinfo.u_name',"describe","c_career","ic.company_address",DB::raw("date_format(ic.time,'%Y-%m-%d %H:%i') as times"),'ic.company')
            ->orderBy('times')
            ->paginate(10);
        $data['error']=0;
        $data['other']=$ic;
        return json_encode($data);
    }
    //面试资料搜索
    public function IC_search(Request $request){
        $company  = $request->get("company");
        $times    = $request->get("times");
        $username = $request->get("username");
        if($company || $times || $username){
            if($company){
                $where['company']=$company;
            }if($username){
                $where['u_name']=$username;
            }
            if($times){
                $ic=DB::table('ic')
                    ->leftjoin('userinfo','ic.u_id','=','userinfo.u_id')
                    ->join("users","userinfo.u_id","=","users.user_id")
                    ->join("career","career.c_id","=","user_job")
                    ->where($where)->where(DB::raw("date_format(time,'%Y-%m-%d')"),$times)
                    ->select("describe",'userinfo.u_name',"c_career","ic.company_address",DB::raw("date_format(ic.time,'%Y-%m-%d %H:%i') as times"),'ic.company')
                    ->orderBy('times')
                    ->get();
            }else{
                $ic=DB::table('ic')
                    ->leftjoin('userinfo','ic.u_id','=','userinfo.u_id')
                    ->join("users","userinfo.u_id","=","users.user_id")
                    ->join("career","career.c_id","=","user_job")
                    ->where($where)
                    ->select("describe",'userinfo.u_name',"c_career","ic.company_address",DB::raw("date_format(ic.time,'%Y-%m-%d %H:%i') as times"),'ic.company')
                    ->get();
            }
            if($ic){
                $msg = array(
                    "error"=>0,
                    "data"=>$ic,
                );
            }else{
                $msg = array(
                    "error"=>"2002",
                    "msg"=>"暂无信息",
                );
            }

            return json_encode($msg);
        }else{
            $msg=array(
                "data"=>'无参数',
                "info"=>'请求错误',
                "error"=>"2001"
            );
            return json_encode($msg);
        }
    }

    //面试资料添加接口
    public function msdata(Request $request){
        $data=$request->all();
        $u_id=$data['u_id'];
        $company=$data['company'];
        $company_address=$data['company_address'];
        $time=$data['time'];
        $describe = $data['describe'];
        if(!empty($u_id) && !empty($company) && !empty($time) && !empty($company_address) && !empty($describe)){
            //入库
            $add_data=DB::table('ic')->insert(
                [
                    'u_id' => $u_id,
                    'company' => $company,
                    'time'=>$time,
                    'company_address'=>$company_address,
                    'describe'=>$describe
                ]);
            if($add_data){
                $msg=array(
                    "info"=>'成功',
                    "data"=>'面试资料入库成功',
                    "error"=>'1000'
                );
                return json_encode($msg);
            }else{
                $msg=array(
                    "info"=>'失败',
                    "data"=>'面试资料入库失败',
                    "error"=>'1013'
                );
                return json_encode($msg);
            }
        }else{
            //信息填写不完整
            $msg=array(
                "info"=>'信息填写不完整',
                "data"=>'参数不能为空',
                "error"=>'1012'
            );
            return json_encode($msg);
        }
    }
    ////方法模块 显示数据
    public function showffdata(Request $request){
        $data=$request->all();
        $at_id=$data['at_id'];
        $top=$data['top'];
        if($at_id){
            if($top){
                //有分类下的最热

                $article = DB::table('article')
                    ->join('users', 'article.a_adduser', '=', 'users.user_id')
                    ->leftJoin('ar_type', 'article.a_type', '=', 'ar_type.at_id')
                    ->select('users.user_name', 'a_id', 'a_title', 'at_type', 'a_con', 'a_addtime', 'a_num', 'brows', 'a_pingnum')
                    ->where("article.a_type", $data['at_id'])
                    ->where("a_state", 1)
                    ->orderBy('brows', 'desc')
                    ->get();
                foreach($article as $key=>$v){
                    $article[$key]["a_con"] = htmlspecialchars($v['a_con']);
                }
                if($article){
                    $msg=array(
                        "info"=>'成功展示数据',
                        "data"=>$article,
                        "error"=>'1000'
                    );
                    return json_encode($msg);
                }else{
                    $msg=array(
                        "info"=>'数据为空',
                        "data"=>'有分类下的最热没有数据',
                        "error"=>'1014'
                    );
                    return json_encode($msg);
                }
            }
            //有分类下的最新
            $article = DB::table('article')
                ->join('users', 'article.a_adduser', '=', 'users.user_id')
                ->leftJoin('ar_type', 'article.a_type', '=', 'ar_type.at_id')
                ->select('users.user_name', 'a_id', 'a_title', 'at_type', 'a_con', 'a_addtime', 'a_num', 'brows', 'a_pingnum')
                ->where("article.a_type", $data['at_id'])
                ->where("a_state", 1)
                ->orderBy('a_id', 'desc')
                ->get();
            foreach($article as $key=>$v){
                $article[$key]["a_con"] = htmlspecialchars($v['a_con']);
            }
            if($article){
                $msg=array(
                    "info"=>'成功展示数据',
                    "data"=>$article,
                    "error"=>'1000'
                );
                return json_encode($msg);
            }else{
                $msg=array(
                    "info"=>'数据为空',
                    "data"=>'有分类下的最新没有数据',
                    "error"=>'1015'
                );
                return json_encode($msg);
            }
        }else{
            if($top){
                //全部分类下的最热

                $article = DB::table('article')
                    ->join('users', 'article.a_adduser', '=', 'users.user_id')
                    ->leftJoin('ar_type', 'article.a_type', '=', 'ar_type.at_id')
                    ->select('users.user_name', 'a_id', 'a_title', 'at_type', 'a_con', 'a_addtime', 'a_num', 'brows', 'a_pingnum')
                    ->where("a_state", 1)
                    ->orderBy('brows', 'desc')
                    ->get();
                foreach($article as $key=>$v){
                    $article[$key]["a_con"] = htmlspecialchars($v['a_con']);
                }
                if($article){
                    $msg=array(
                        "info"=>'成功展示数据',
                        "data"=>$article,
                        "error"=>'1000'
                    );
                    return json_encode($msg);
                }else{
                    $msg=array(
                        "info"=>'数据为空',
                        "data"=>'全部分类下的最热没有数据',
                        "error"=>'1016'
                    );
                    return json_encode($msg);
                }
            }
            //全部分类下的最新
            $article = DB::table('article')
                ->join('users', 'article.a_adduser', '=', 'users.user_id')
                ->leftJoin('ar_type', 'article.a_type', '=', 'ar_type.at_id')
                ->select('users.user_name', 'a_id', 'a_title', 'at_type', 'a_con', 'a_addtime', 'a_num', 'brows', 'a_pingnum')
                ->where("a_state", 1)
                ->orderBy('a_id', 'desc')
                ->get();
            header("content-type:text/html;charset=utf8");
            foreach($article as $key=>$v){
                $article[$key]["a_con"] = htmlspecialchars($v['a_con']);
            }
            print_r($article);die;
            if($article){
                $msg=array(
                    "info"=>'成功返回数据',
                    "data"=>$article,
                    "error"=>'1000'
                );
                return json_encode($msg);
            }else{
                $msg=array(
                    "info"=>'数据为空',
                    "data"=>'全部分类下的最新没有数据',
                    "error"=>'1017'
                );
                return json_encode($msg);
            }
        }

    }
    //答疑模块添加提问
    public function add_questions(Request $request){
        $data=$request->all();
        $t_title=$data['t_title'];
        $t_content=$data['t_content'];
        $user_id=$data['user_id'];
        $d_id=$data['d_id'];
        $add_time=$data['add_time'];
        if(!empty($t_title) && !empty($t_content) && !empty($t_content) && !empty($user_id) && !empty($d_id) && !empty($t_title) && !empty($add_time)){
            //添加数据库
            $arr=DB::table('t_tw')->insert(
                [
                    't_title' => $t_title,
                    't_content' => $t_content,
                    'user_id'=> $user_id,
                    'd_id'=> $d_id,
                    'add_time'=>$add_time
                ]);
            if($arr){
                //添加成功
                $msg=array(
                    "info"=>'成功',
                    "data"=>'添加数据成功',
                    "error"=>'1000'
                );
                return json_encode($msg);
            }else{
                //添加失败
                $msg=array(
                    "info"=>'失败',
                    "data"=>'添加数据失败',
                    "error"=>'1019'
                );
                return json_encode($msg);
            }
        }else{
            $msg=array(
                "info"=>'参数不完整',
                "data"=>'参数不能为空',
                "error"=>'1018'
            );
            return json_encode($msg);
        }
    }
}
