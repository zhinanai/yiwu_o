<?php
/**
 * Created by PhpStorm.
 * User: xuechunxuan
 * Date: 2018/9/3
 * Time: 19:42
 */
namespace Common\Model;

class Answer extends BaseModel{
    public function __construct()
    {
      $this->setTableName("answer");
    }
}