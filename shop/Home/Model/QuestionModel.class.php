<?php
/**
 * 问题模型
 */

namespace Home\Model;

use Common\Model\BaseModel;

class Question extends BaseModel
{
    public function __construct()
    {
        $this->setTableName("question");
    }
}