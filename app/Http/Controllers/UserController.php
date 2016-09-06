<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Captcha;
use Cache;
use Illuminate\Support\Facades\Mail; 
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /*答疑最新数据*/
    public function wenda(){
                	header("content-type:text/html;charset=utf-8");
                    $data=DB::table('t_tw')
                        ->select(DB::raw('*,count(comments.com_id) as num ,t_tw.t_id'))
                        ->join('direction', function ($join) {
                            $join->on('direction.d_id', '=', 't_tw.d_id');
                        })
                        ->leftjoin('users', function ($join) {
                            $join->on('users.user_id', '=', 't_tw.user_id');
                        })
                        ->leftjoin('comments',function($join){
                            $join->on('comments.t_id', '=', 't_tw.t_id');
                        })
                        ->groupby('t_tw.t_id')
                        ->orderBy('num','desc')
                        ->get();
                       // print_r($data);die;
                    if($data){
		                $result=array(
		                    'error'=>0,
		                    'msg'=>'ok',
		                    'data'=>$data
		                );
		            }else{
		                $result=array(
		                    'error'=>1,
		                    'msg'=>'参数有误'
		                );
		            }
                    $content=json_encode($result);
                    return $content;
                }
    /*答疑最新数据*/
    public function bestnew(){
        header("content-type:text/html;charset=utf-8");
        $data=DB::table('t_tw')
            ->join('direction', function ($join) {
                $join->on('direction.d_id', '=', 't_tw.d_id');
            })
            ->join('users', function ($join) {
                $join->on('users.user_id', '=', 't_tw.user_id');
            })
            ->orderBy('t_tw.add_time', 'desc')
            ->get();
        	if($data){
		                $result=array(
		                    'error'=>0,
		                    'msg'=>'ok',
		                    'data'=>$data
		                );
		            }else{
		                $result=array(
		                    'error'=>1,
		                    'msg'=>'参数有误'
		                );
		            }
                      if($data){
        	$result=array(
		                    'error'=>0,
		                    'msg'=>'ok',
		                    'data'=>$data
		                );
		            }else{
		                $result=array(
		                    'error'=>1,
		                    'msg'=>'参数有误'
		                );
		            }
                    $content=json_encode($result);
                    return $content;
    }
    /* 答疑未回答*/
    public function waitreply(){
        $data=DB::table('t_tw')
            ->select('*', DB::raw("count(comments.com_id) as num"),'t_tw.t_id')
            ->leftjoin('direction', function ($join) {
                $join->on('direction.d_id', '=', 't_tw.d_id');
            })
            ->leftjoin('users', function ($join) {
                $join->on('users.user_id', '=', 't_tw.user_id');
            })
            ->leftjoin('comments', function ($join) {
                $join->on('comments.t_id', '=', 't_tw.t_id');
            })
            ->groupby('t_tw.t_id')
            ->havingRaw('count(comments.com_id)=0')
            ->get();
            //header("content-type:text/html;charset=utf-8");
            // foreach ($data as $key => $value){
            // 	foreach($value as $l=>$e){
            // 		$value->$l=str_replace("<p>","",$e);
            // 	}
            // }
            // foreach ($data as $key => $value){
            // 	foreach($value as $l=>$e){
            // 		$value->$l=str_replace("</p>","",$e);
            // 	}
            // }
            //print_r($data);die;
           
          if($data){
        	$result=array(
		                    'error'=>0,
		                    'msg'=>'ok',
		                    'data'=>$data
		                );
		            }else{
		                $result=array(
		                    'error'=>1,
		                    'msg'=>'参数有误'
		                );
		            }
                    $content=json_encode($result);
                    return $content;
    }
    /*
     * 方法模块接口
     */
    //最新最热  默认是最新
    public function article(Request $request)
    {
        $at_id=$request->get('at_id');
        $top=$request->get('top');
        if($at_id){
            if($top){
                $article = DB::table('article')
                    ->join('users', 'article.a_adduser', '=', 'users.user_id')
                    ->leftJoin('ar_type', 'article.a_type', '=', 'ar_type.at_id')
                    ->select('users.user_name', 'a_id', 'a_title', 'at_type', 'a_con', 'a_addtime', 'a_num', 'brows', 'a_pingnum')
                    ->where("article.a_type", $at_id)
                    ->where("a_state", 1)
                    ->orderBy('brows', 'desc')
                    ->get();
                if($article){
                    $result=array(
                        'status'=>0,
                        'msg'=>'ok',
                        'data'=>$article
                    );
                }else{
                    $result=array(
                        'status'=>1,
                        'msg'=>'参数有误'
                    );
                }
            }
            $article = DB::table('article')
                ->join('users', 'article.a_adduser', '=', 'users.user_id')
                ->leftJoin('ar_type', 'article.a_type', '=', 'ar_type.at_id')
                ->select('users.user_name', 'a_id', 'a_title', 'at_type', 'a_con', 'a_addtime', 'a_num', 'brows', 'a_pingnum')
                ->where("article.a_type", $at_id)
                ->where("a_state", 1)
                ->orderBy('a_id', 'desc')
                ->get();
            if($article){
                $result=array(
                    'status'=>0,
                    'msg'=>'ok',
                    'data'=>$article
                );
            }else{
                $result=array(
                    'status'=>1,
                    'msg'=>'参数有误'
                );
            }

        }else{
            if(isset($top)){
                $article = DB::table('article')
                    ->join('users', 'article.a_adduser', '=', 'users.user_id')
                    ->leftJoin('ar_type', 'article.a_type', '=', 'ar_type.at_id')
                    ->select('users.user_name', 'a_id', 'a_title', 'at_type', 'a_con', 'a_addtime', 'a_num', 'brows', 'a_pingnum')
                    ->where("a_state", 1)
                    ->orderBy('brows', 'desc')
                    ->get();
                if($article){
                    $result=array(
                        'status'=>0,
                        'msg'=>'ok',
                        'data'=>$article
                    );
                }else{
                    $result=array(
                        'status'=>1,
                        'msg'=>'参数有误'
                    );
                }

            }
            $article = DB::table('article')
                ->join('users', 'article.a_adduser', '=', 'users.user_id')
                ->leftJoin('ar_type', 'article.a_type', '=', 'ar_type.at_id')
                ->select('users.user_name', 'a_id', 'a_title', 'at_type', 'a_con', 'a_addtime', 'a_num', 'brows', 'a_pingnum')
                ->where("a_state", 1)
                ->orderBy('a_id', 'desc')
                ->get();
            if($article){
                $result=array(
                    'status'=>0,
                    'msg'=>'ok',
                    'data'=>$article
                );
            }else{
                $result=array(
                    'status'=>1,
                    'msg'=>'参数有误'
                );
            }
        }
        return json_encode($result);
    }

    //方法模块用户评论
    public function userContent(Request $request)
    {
        //用户id
        $u_id=$request->get('uid');
        //文章id
        $a_id=$request->get('a_id');
        //评论内容
        $ap_con=$request->get('ap_con');
        //时间
        $ap_addtime=$request->get('article_addtime');
        if(empty($u_id)||empty($a_id)||empty($ap_con)||empty($ap_addtime)){
            $result=array(
                "error"=>"1011",
                "data"=>"请填写正规的参数"
            );
        }else{
            //添加到用户评论表中aping
            $sql = "insert into aping(u_id,ap_con,a_id,ap_addtime) values('$u_id','$ap_con','$a_id','$ap_addtime')";
            $re = DB::insert($sql);
            if($re){
                $res=DB::update("update article set a_pingnum=a_pingnum+1 where a_id='$a_id'");
                if($res){
                    //$arr=DB::table('article')->where("a_id",$a_id)->get();
                    $result=array(
                        "error"=>"1000",
                        "info"=>"操作成功",
                        "data"=>"评论成功"
                    );
                }
                else{
                    $result=array(
                        "error"=>"1012",
                        "info"=>"操作失败",
                        "data"=>"评论失败"
                    );
                }
            }
        }
        //判断是否评论成功
        return json_encode($result);
    }

    //用户方法模块点赞
    public function userZan(Request $request)
    {
        $article_id=$request->get("a_id");
        $u_id=$request->get("uid");
        if(empty($u_id)||empty($article_id)){
            $result=array(
                'status'=>1011,
                'data'=>'操作错误',
                'msg'=>'请输入正规的参数'
            );
        }else{
            //查询数据库是否点过赞
            $zan_data = DB::table('article_zan')
                ->where(['u_id'=>$u_id,'article_id'=>$article_id])
                ->first();
            if($zan_data){
                $result=array(
                    'status'=>1003,
                    'data'=>'已点过赞',
                    'msg'=>'当前用户已点过赞'
                );
            }else{
                //点赞 添加数据库
                $add_zdata=DB::table('article_zan')->insert(
                    [
                        'u_id' => $u_id,
                        'article_id' => $article_id
                    ]);
                if($add_zdata){
                    //修改点赞数量
                    $up_zannum = DB::update("update article set a_num = a_num+1 where a_id = $article_id and a_state =1");
                    if($up_zannum){
                        $result=array(
                            'status'=>1000,
                            'data'=>'操作成功',
                            'msg'=>'点赞成功'
                        );
                    }else{
                        $result=array(
                            'status'=>1014,
                            'data'=>'操作失败',
                            'msg'=>'修改点赞数量失败'
                        );
                    }

                }else{
                    $result=array(
                        'status'=>1013,
                        'data'=>'操作失败',
                        'msg'=>'点赞失败'
                    );
                }
            }
        }
        return json_encode($result);
    }

    /*
* 上传个人简历信息
*/
    public function userResume(Request $request)
    {
        //$arr=$request->input();
        //接收值
        $r_name=$request->input('r_name');
        $r_sex=$request->input('r_sex');
        $r_birthdy=$request->input('r_birthdy');
        $r_worktime=$request->input('r_worktime');
        $r_nowlive=$request->input('r_nowlive');
        $r_oldlive=$request->input('r_oldlive');
        $r_phone=$request->input('r_phone');
        $r_email=$request->input('r_email');
        $file = $request->file('fileField');
        //如果没有上传个人简历的头像
        if(empty($file)||empty($r_name)||empty($r_sex)||empty($r_birthdy)||empty($r_worktime)||empty($r_nowlive)||empty($r_oldlive)||empty($r_phone)||empty($r_email)){
            $result=array(
                "status"=>"1012",
                "info"=>"请输入正确的参数",
                "data"=>"参数有误"
            );
            //print_r($result);
            return json_encode($result);
        }else{
            $allowed_extensions = ["png", "jpg", "gif","JPG"];
            //如果上传出错,返回错误信息
            if ($file->getClientOriginalExtension() && !in_array($file->getClientOriginalExtension(), $allowed_extensions))
            {
                $result=array(
                    "status"=>"1013",
                    "info"=>"头像上传错误",
                    "data"=>"文件上传错误"
                );
                return json_encode($result);
            }
            //存放的路径
            $destinationPath = 'static/images';
            //获取图片后缀名
            $extension = $file->getClientOriginalExtension();
            //设置图片名称
            $uid=$request->input("uid");
            $code=md5($uid);
            $fileName = $code.'.'.$extension;
            if($file->move($destinationPath, $fileName))
            {
                /*
                 * 个人信息简历入库
                 */
                //获取文件的全名
                $user_filedir = $destinationPath.$fileName;
                $re=DB::table('resume')->insert([
                    'r_picture' =>$destinationPath,
                    'r_name' =>$r_name,
                    'r_sex' =>$r_sex,
                    'r_birthdy' =>$r_birthdy,
                    'r_worktime' =>$r_worktime,
                    'r_nowlive' =>$r_nowlive,
                    'r_oldlive' =>$r_oldlive,
                    'r_phone' => $r_phone,
                    'r_email' =>$r_email,
                    'u_id'=>$uid
                ]);
                if($re){
                    $result=array(
                        "status"=>"1000",
                        "info"=>"简历上传成功",
                        "data"=>"操作成功"
                    );
                    //print_r($result);
                    return json_encode($result);
                }else{
                    $result=array(
                        "status"=>"1014",
                        "info"=>"简历上传失败",
                        "data"=>"操作失败"
                    );
                    //print_r($result);
                    return json_encode($result);
                }

            }
        }

    }

    //个人中心修改头像
    public function set_headpic(Request $request){
        $datas = $request->file("fileField");
        $user_id=$request->input('user_id');
        //echo $user_id;die;
        //print_r($datas);
        if(empty($datas)){
            $msg=array(
                "info"=>'参数不完整',
                "data"=>'请重新上传',
                "error"=>'1021'
            );
            return json_encode($msg);
        }else{
            $a_addtime=date("Y-m-d H:i:s");
            $file = $request->file('fileField');
            $allowed_extensions = ["png", "jpg", "gif","JPG"];
            //如果上传出错,返回错误信息
            if ($file->getClientOriginalExtension() && !in_array($file->getClientOriginalExtension(), $allowed_extensions))
            {
                return ['error' => 'You may only storage png, jpg or gif.'];
            }
            $destinationPath = 'picture/';
            //获取图片后缀名
            $extension = $file->getClientOriginalExtension();
            //设置图片名称


            $code=md5($user_id);
            $fileName = $code.'.'.$extension;
            //print_r($fileName);die;

            if($file->move($destinationPath, $fileName))
            {
                /*
                 * 设置用户头像字段入库
                 */
                $user_filedir = $destinationPath.$fileName;
                $sql = "update users set user_filedir = '$user_filedir' where user_id = '$user_id'";
                $upd = DB::select($sql);
                $ses = DB::table("users")->where("user_id",1)->select("user_filedir")->first();
                $sed="http://123.56.249.121/mcanonapi/public/picture/".$ses['user_filedir'];
                /*
                 * 修改用户头像重新设置session
                 */
                $msg=array(
                    "info"=>'成功',
                    "data"=>$sed,
                    "error"=>'1000'
                );
                return json_encode($msg);
            }
        }

    }

    //个人中心实名认证
    public function setmsg(Request $request){
        $u_id=$request->get('uid');
        $u_name=$request->get('u_name');
        $id_card=$request->get('id_card');
        if(!preg_match("/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}(\d|x|X)$/", $id_card)){
            $data = array(
                "error"=>12444,
                "info"=>"身份证号码不正确"
            );
            return json_encode($data);
        }
        if (!preg_match("/^[\x{4e00}-\x{9fa5}]{2,4}$/u",$u_name)) {
            $data = array(
                "error"=>22332,
                "info"=>"实名姓名必须为中文4位以内"
            );
            return json_encode($data);
        }

        if(!empty($u_id) && !empty($u_name) && !empty($id_card)){
            $ds = DB::table("users")->where("user_id",$u_id)->first();
            //判断用户是否存在
            if($ds==""){
                $data = array(
                    "error"=>12232,
                    "info"=>"该用户不纯在"
                );
                return json_encode($data);
            }

            $ksl = DB::table("userinfo")->where("u_id",$u_id)->get();
            if($ksl){
                $data = array(
                    "error"=>10001,
                    "info"=>"该用户已经实名"
                );
                return json_encode($data);
            }else{
                $see = DB::insert("insert into userinfo (u_id,u_name,u_idcard) VALUE ('$u_id','$u_name','$id_card')");
                $ksl = DB::table("userinfo")->where("u_id",$u_id)->get();
                $data = array(
                    "msg"=>10000,
                    "info"=>"实名成功",
                    "data"=>$ksl
                );
                return json_encode($data);
            }
        }else{
            $data = array(
                "error"=>23315,
                "info"=>"参数有误,有空字段"
            );
            return json_encode($data);
        }

    }

    //个人中心修改资料接口
    public function set_data(Request $request){
        $data=$request->all();
        //用户id  昵称  职位  性别  个性签名
        $user_id=isset($data['user_id'])?$data['user_id']:"";
        $user_name=isset($data['user_name'])?$data['user_name']:"";
        $user_job=isset($data['user_job'])?$data['user_job']:"";
        $user_sex=isset($data['user_sex'])?$data['user_sex']:"";
        $user_aboutme=isset($data['user_aboutme'])?$data['user_aboutme']:"";

        if(!empty($user_id) && !empty($user_name) && !empty($user_job) && !empty($user_sex) && !empty($user_aboutme)){
            if(!preg_match("/^(\d)+$/",$user_id)){
                $msg=array(
                    "info"=>'参数有误user_id不正确',
                    "error"=>'10032'
                );
                return json_encode($msg);
            }
            if(!preg_match("/^(\d)+$/",$user_sex)){
                $msg=array(
                    "info"=>'参数有误性别必须位数字',
                    "error"=>'10033'
                );
                return json_encode($msg);
            }
            if(!preg_match("/^(\d)+$/",$user_job)){
                $msg=array(
                    "info"=>'参数有误职位必须位数字',
                    "error"=>'10034'
                );
                return json_encode($msg);
            }
            //修改数据库
            $upd=DB::table('users')
                ->where('user_id', $user_id)
                ->update([
                    'user_name' => $user_name,
                    'user_job' => $user_job,
                    'user_sex' => $user_sex,
                    'user_aboutme'=>$user_aboutme
                ]);
            $msg=array(
                "info"=>'修改成功',
                "msg"=>'1000'
            );
            return json_encode($msg);
        }else{
            if($user_id!="" and $user_name=="" and $user_job=="" and $user_sex=="" and $user_aboutme==""){
                $db = DB::table("users")
                    ->join("career","users.user_job","=","career.c_id")
                    ->where("user_id",$user_id)
                    ->select("user_id","user_name","career.c_career","user_sex","user_aboutme")
                    ->first();
                if($db['user_sex']==""){
                    $db['user_sex']="保密";
                }elseif($db['user_sex']==1){
                    $db['user_sex']="男";
                }elseif($db['user_sex']==2){
                    $db['user_sex']="女";
                }
                $arrs = array(
                    "data"=>$db,
                    "msg"=>10003
                );
                return json_encode($arrs);
            }
        }



    }

}