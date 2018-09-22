<?php
namespace Home\Controller;

/*
 * 问答区
 * */
class QuestionController  extends CommonController{

    private function getQuestion(){
        return M("Question");
    }

    private function getAnswer(){
        return M("Answer");
    }

    private function getStore(){
        return M("Store");
    }

    private function getTranMoney(){
        return M('tranmoney');
    }
    private function getUserStore(){
        return new \Home\Model\StoreModel();
    }

    /*
     * 我的所有问答
     * */
    public function myAllQuestion(){

    }

    /*
     * 内容跟帖
     * @param $questionId int 问题Id
     * */
    public function questionDetail(){
        if(IS_POST){
            $answerArr=I("post.");
            if(is_array($answerArr)) {//对问题进行回答
                $data = [
                    "id"=>"",
                    'question_id' => $answerArr['questionId'],
                    'uid' => session("userid"),
                    'content' => $answerArr['content'],
                    'answer_time'=>time()
                ];
               echo $this->getAnswer()->add($data) ? 1 : 0;
            }
        }elseif(IS_GET){
           $questionId=I("get.questionId");
           if(is_numeric($questionId)){
            $qusetionArr=$this->getAnswer()
                ->alias("a")
                ->join("ysk_user b")
                ->where(["a.question_id"=>$questionId])
                ->where("a.uid=b.userid")
                ->field("a.*,b.username,b.img_head")
                ->order("a.is_it_best,a.praise,a.id desc")->select();
           echo $this->htmlDecode($qusetionArr,"answer_time");
           }
     }
    }

    /*
     * 采纳回答
     * */
    public function adoptQuestion(){
        if(IS_POST){
            $questionId=I("post.questionId");
            $answerId=I("post.answerId");//
            $answerUserId=I("post.answerUserId");//回答用户Id
            if(is_numeric($questionId)&&is_numeric($answerId)) {
                //判断问题是否已结束
                $questionInfo=$this->getQuestion()->where(['id'=>$questionId])->field("uid,amount,status")->find();
                if ($questionInfo['status']==2) {
                    $answerUserId = $answerUserId ?  $answerUserId : 0;
                    $userJiFen=$this->getUserStore()->jiFenNum(['uid'=>$answerUserId]);
                    //不能采纳自己的答案
                    if($answerUserId==$questionInfo['uid']){
                        echo  json_encode(['msg'=>'不能标记本身为最佳答案']);
                        exit();
                    }
                    $data2=[
                        'pay_id'=>$answerUserId,
                        'get_id'=>$answerUserId,
                        'get_nums'=>$questionInfo['amount'],
                        'get_time'=>time(),
                        'get_type'=>26,//采纳奖励
                        'now_nums'=>$userJiFen+$questionInfo['amount'],
                        'now_nums_get'=>$userJiFen+$questionInfo['amount'],
                        'is_release'=>1
                    ];
                     //执行积分奖励
                     if($this->getStore()->where(['uid'=>$answerUserId])->setInc("fengmi_num",$questionInfo['amount'])){
                         //增加明细
                         $this->getTranMoney()->add($data2);
                         //更新问题状态
                         $this->getQuestion()->where(['id'=>$questionId])->save(['status'=>1]);//标记问题已结束
                         $this->getAnswer()->where(['id'=>$answerId])->save(['is_it_best'=>1]);//标记回答被采纳
                        echo 1;
                     }else{
                         echo  json_encode(['msg'=>'增加积分出错请联系管理员']);
                     }
                }else{
                  echo  json_encode(['msg'=>'该问题已经结束']);
                }
            }
        }
    }

    /*
     *点赞回复 24小时内点赞一次
     * @param answerId  int  回复序列id
     * */
    public function praise(){
        if(IS_POST){
             $answerId=I("answerId");
             if(is_numeric($answerId)){
                 if(cookie("praise".$answerId)){
                     //当天该问题已经赞过
                 }else {
                     //执行点赞操作

                     //设置cookie
                     cookie("praise".$answerId,1,86400);
                 }
             }
        }
    }

    /*
     * 搜索热更新
     * */
    public function hostQuestion(){
        if(IS_POST){
            $qusetionList=$this->getQuestion()->limit(8)->field("id,title")->order("pv desc")->select();
            echo $this->htmlDecode($qusetionList);
        }else{
        }
    }

    /**
     * 问题页
     */
    public function question()
    {
        if(IS_GET) {
            $this->display();
        }elseif(IS_POST){
            $id=I("post.id");
            if(is_numeric($id)){
                $this->getQuestion()->where(['id'=>$id])->setInc("pv");//该问题pv访问+1
                $questionInfo= $this->getQuestion()
                    ->alias("a")
                    ->join("ysk_user b")
                    ->field("a.*,b.username")
                    ->where("a.uid=b.userid")
                    ->where(['id'=>$id])->select();
                $questionInfo[0]['is_show']=$questionInfo[0]['uid']==session("userid") ? 1 : 2;//是题主本人显示采纳回答
              echo  $this->htmlDecode($questionInfo) ;
            }else{
                echo null;
            }

        }
    }
    public function htmlDecode($qusetionArr,$time='start_time'){
        $i=0;
        foreach ($qusetionArr as $key){
            $qusetionArr[$i]['content']=html_entity_decode($qusetionArr[$i]['content']);
            $qusetionArr[$i][$time]=date("Y-m-d H:i",$qusetionArr[$i][$time]);
            $i++;
        }
        return json_encode($qusetionArr);
    }
    /* 问题列表 */
    public function questionlist()
    {
        if(IS_GET) {
            $this->assign("questionArr",$this->getQuestion()->limit(10)->order("id desc")->select());
            $this->display();
        }elseif(IS_POST){
            if(I("post.model")==1){
                $questionId=I("post.questionId");
                //下拉刷新
                $questionList=$this->getQuestion()
                    ->alias("a")
                    ->join("ysk_user b")
                    ->field("a.*,b.username")
                    ->where("a.uid=b.userid")
                    ->where("id>$questionId")->limit(10)->order("id desc")->select();
                /*
                 $this->getQuestion()
                    ->alias("a")
                    ->join("ysk_answer b")
                    ->field("a.*,b.content mesg")
                    ->where("a.id=b.question_id")
                    ->where("a.id",">",$questionId)
                    ->limit(10)
                    ->order("a.id,b.is_it_best desc")
                    ->group("a.id")
                    ->select();
                 * */
            }else{
                //上拉加载
                $page=I("post.page");
                $limit=10;
                $questionList= $this->getQuestion()
                    ->alias("a")
                    ->join("ysk_user b")
                    ->field("a.*,b.username")
                    ->where("a.uid=b.userid")
                    ->limit($page*$limit,$limit)->order("id desc")->select();

            }
            echo $this->htmlDecode($questionList);
        }
    }
    /* 搜索 */
    public function search()
    {
        if(IS_GET) {
            $this->display();
        }elseif(IS_POST){
        }
    }
    /* 搜索 */
    public function searchTitle()
    {
        if(IS_GET) {
            $this->display();
            $searchValue=I("get.search");
            $qusetionList=$this->getQuestion()->limit(8)->field("id,title")->where("title like '%$searchValue%'")->order("id desc")->select();
            echo $this->htmlDecode($qusetionList);
        }elseif(IS_POST){
            $searchValue=I("post.search");
            $page=I("post.page");
            $limit=10;
            $qusetionList=$this->getQuestion()->limit($page*$limit,$limit)->where("title like '%$searchValue%'")->order("id desc")->select();
            echo $this->htmlDecode($qusetionList);
        }
    }


    /*
     * 提问
     * */
    public function report(){
        if(IS_GET) {
            return $this->display();
        }elseif(IS_POST){
           $questionArr=I("post.");

          if(!empty($questionArr['title'])){
              //判断用户积分是否充足
              $userAmount=M("Store")->where(["uid"=>session("userid")])->field("fengmi_num,cangku_num")->find();
              $fengmiNum=$userAmount['fengmi_num'];
              $amount=$questionArr['amount']?$questionArr['amount'] : 0;//提问积分
              if($fengmiNum<$amount){
                  echo json_encode(['msg'=>'可用积分不足']);
              }else {
                  $amount=$questionArr['amount'] ? $questionArr['amount'] : 0;
                  $data = [
                      "title" => $questionArr['title'],
                      "content" => $questionArr['content'],
                      'amount' => $amount,
                      'uid' => session('userid'),
                      'start_time' => time(),
                      'thumbnail' => str_replace("&quot;", "", $this->getThumbanail($questionArr['content'])),
                  ];
                  $data2=[
                      'pay_id'=>session("userid"),
                      'get_id'=>session("userid"),
                      'get_nums'=>"-".$amount,
                      'get_time'=>time(),
                      'get_type'=>25,//问题奖励
                      'now_nums'=>$userAmount['fengmi_num']-$amount,
                      'now_nums_get'=>$userAmount['fengmi_num']-$amount,
                      'is_release'=>1
                  ];

                  //扣除对应积分并返回添加的问题ID
                  if (M("Store")->where(["uid" => session("userid")])->setDec("fengmi_num", $amount)) {
                      //写明细进积分记录
                       $this->getTranMoney()->add($data2);
                      echo $this->getQuestion()->add($data);
                  }
              }
          }
        }
    }

    /*
     * 匹配内容中的预览图多图返回第一张
     * return imgPath string  预览图路径
     * */
    public function getThumbanail($content){
       $prc='/(href|src)=([\"|\']?)([^\"\'>]+.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG))/i';
        preg_match_all($prc, $content, $out);
        return !empty($out[3][0]) ? $out[3][0] : "" ;//返回第一张正确图片地址
    }

    /*
     * 转换图片
     * return imgPath 图片保存路径
     * */
    public function base64ToImg($base64){

        if(stripos($base64,"data:image/png;base64,")>-1){
            $str="data:image/png;base64,";
            $ext="png";
        }elseif(stripos($base64,"data:image/jpeg;base64,")>-1){
            $str="data:image/jpeg;base64,";
            $ext="jpg";
        }elseif(stripos($base64,"data:image/gif;base64,")>-1){
            $str="data:image/gif;base64,";
            $ext="gif";
        }
        $imgStr=base64_decode(str_replace($str,"",$base64));//解码图片为二进制流
        $filePath="./Public/question/".date("Y-m-d",time());//文件保存目录
        if (!is_dir($filePath)) mkdir($filePath);//文件夹不存在则创建
        $fileName=md5(time()+mt_rand(1,100));//md5随机加密
        $imgPath=$filePath."/$fileName.".$ext;//完整图片路径
        echo file_put_contents($imgPath,$imgStr) ? json_encode(str_replace("./P","/P",$imgPath)) : null;//写入文件并返回数据
    }

    /*
     * 获取用户基本信息
     * 头像昵称id
     * */
    public function getUserInfo(){
        $uid=session("userid");
        if(IS_POST){
              $userInfo=M("User")->where(['userid'=>session("userid")])->field("userid,username,img_head")->select();
            $userQ=$this->getQuestion()->where("uid=$uid")->count();//发起的提问
            $userA=$this->getAnswer()->where("uid=$uid")->count();//参与的回答
            echo json_encode(['userInfo'=>$userInfo,'userQ'=>$userQ,'userA'=>$userA]);//返回数据
        }
    }

    /*
     * 获取用户参与跟提问的总数
     * */
    public function userQA(){
        $uid=session("userid");
        if(IS_GET){

        }elseif(IS_POST){
            $page=I("post.page");
            $mode=I("post.model");//1参与 2提问
            $limit=10;
            if($mode==1){

                $qusetionList=$this->getAnswer()
                    ->alias("a")
                    ->join("ysk_question b")
                    ->field("a.*,b.title,b.id")
                    ->limit($page*$limit,$limit)
                    ->where("a.question_id=b.id")
                    ->where(['a.uid'=>$uid])
                    ->order("a.is_it_best asc,a.answer_time desc")
                    ->group("b.id")
                    ->select();
                echo  $this->htmlDecode($qusetionList,"answer_time");
            }else{
                $qusetionList=$this->getQuestion()->limit($page*$limit,$limit)
                    ->where(['uid'=>$uid])->order("status asc,start_time desc")->select();
                echo  $this->htmlDecode($qusetionList);
            }


        }
    }

}