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
     * 方法模块接口  马天天
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


}