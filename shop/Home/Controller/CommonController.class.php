<?php
/**
 * 本程序仅供娱乐开发学习，如有非法用途与本公司无关，一切法律责任自负！
 */

namespace Home\Controller;

use Org\JpushApi\src\Exceptions\JPush\JPushException;
use Org\JpushApi\src\JPush\Client;
use Org\JpushApi\src\JPush\PushPayload;
use Org\JpushApi\src\JPush\Test;
use Think\Controller;

class CommonController extends Controller
{
    public function _initialize()
    {
        //判断网站是否关闭
        $close = is_close_site();

        if ($close['value'] == 0) {
            success_alert($close['tip'], U('Login/logout'));
        }
        //验证用户登录
        $this->is_user();
    }


    protected function is_user()
    {
        $userid = user_login();
        $user = M('user');
        if (!$userid) {
            $this->redirect('Login/login');
            exit();
        }
        $where['userid'] = $userid;
        $u_info = $user->where($where)->field('status,session_id,check_app')->find();
        //凌晨1点到8点网站维护
        $H = date("H", time());
        if ($H >= 1 && $H < 8 && $u_info['check_app'] == 0) {
            success_alert("凌晨1点到8点站点维护", U('Login/logout'));
        }/*else if($u_info['check_app'] == 0){
            success_alert("站点功能升级请稍后登陆~", U('Login/logout'));
        }*/
        //判断12小时后必须重新登录
        $in_time = session('in_time');
        $time_now = time();
        $between = $time_now - $in_time;
        if ($between > 3600 * 24 * 5) {
            $this->redirect('Login/logout');
        }

        //判断用户是否锁定
        $login_from_admin = session('login_from_admin');//是否后台登录
        if ($u_info['status'] == 0 && $login_from_admin != 'admin') {
            if (IS_AJAX) {
                ajaxReturn('你账号已锁定，请联系管理员', 0);
            } else {
                success_alert('你账号已锁定，请联系管理员', U('Login/logout'));
                exit();
            }
        }

        //判断用户是否在他处已登录
        $session_id = session_id();
        if ($session_id != $u_info['session_id'] && empty($login_from_admin)) {

            if (IS_AJAX) {
                ajaxReturn('您的账号在他处登录，您被迫下线', 0);
            } else {
                success_alert('您的账号在他处登录，您被迫下线', U('Login/logout'));
                exit();
            }
        }
        //记录操作时间
        // session('in_time',time());
    }

    /*
    * 极光推送
     * @param $alias  obj 别名
     * @param $platFrom obj 发送平台(all|所有,ios|苹果,android|安卓)
     * @param $content obj  内容体
    * */
    public function pushJiGuang($alias,$content,$url="",$platFrom='all'){

        $client=new Client("8b0eb871818034d3456f8a3d","349d3e20b396ef18c0c52115");

     if($alias){
         $pusher = $client->push()
             ->addAlias($alias)
             ->setPlatform($platFrom)
             ->setNotificationAlert($content)
             ->androidNotification($content,[
             'extras'=>['url'=>$url]
         ]);
     }else{
         $pusher = $client->push()
             ->setPlatform($platFrom)
             ->setNotificationAlert($content)
             ->androidNotification($content,[
                 'extras'=>['url'=>$url]
             ]);
     }

        try {
           $pusher->send();
        } catch (JPushException $e) {
            // try something else here
            print $e;
        }
    }

    public function getTranMoney(){
        return D("Tranmoney");
    }

    public function getStore(){
        return D("Store");
    }



}

