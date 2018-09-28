<?php
namespace Common\Model;

class TranmoneyModel extends BaseModel {
    public function __construct()
    {
        $this->setTableName("tranmoney");
    }
    /*
     * 创建明细数组
     * @param  $uid int 用户id
     * @param  $amount float 金额
     * @param  $type   int 明细类别(0->转账,1->积分相关【转出80%与转入20%（只记录转入数）】、
     * 【余额对换积分（正数）或积分释放余额（负数）（判断2个UID相等）】,2->积分释放到余额（记录余额）,
     * 3->【余额交易】挂求购,4->购买，5->【余额交易】出售，6->取消，7->购买众筹， 8->买入（增加余额），
     * 9->卖出（减余额），10->取消（卖家返回余额），11->后台操作-余额，12->后台操作-积分，13->余额兑换积分（记录余额），
     * 22->转入的人弹出领取红包 23 推荐赠送 24 购物送积分 25 提问悬赏 26 回答奖励 27 卖出保证金
     * 28 卖出保证金退回 29.见点奖 30.vip1奖励 31.vip2奖励 32.推荐奖
     * 33.流通奖34.见点奖励积分释放 35.推荐奖积分释放 36.流通奖积分释放 37.vip1积分释放 38.vip2积分释放)
     * @param $row 查询字段 1.cangku_num 2.fengmi_num
     * @param $model 模式   +|-
     * */
    public function createArr($uid,$amount,$type,$row=1,$model="+"){

       $userInfo= M("store")->where(['uid'=>$uid])->field($row==1?"cangku_num" : "fengmi_num")->find();//查询用户余额信息
        $amount=round($amount,2);
        return [
            'pay_id'=>$uid,
            'get_id'=>$uid,
            'get_nums'=>$amount,
            'get_time'=>time(),
            'get_type'=>$type,
            'now_nums'=>$userInfo[$row==1?"cangku_num" : "fengmi_num"],
            'now_nums_get'=>$userInfo[$row==1?"cangku_num" : "fengmi_num"],
            'is_release'=>1
        ];
    }
}