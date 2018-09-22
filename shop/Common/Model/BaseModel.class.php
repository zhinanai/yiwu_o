<?php
/**
 * Created by PhpStorm.
 * User: xuechunxuan
 * Date: 2018/9/3
 * Time: 16:56
 */
namespace Common\Model;

class BaseModel extends \Think\Model{
    private $tableName;
    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param mixed $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /*
     * 添加
     * */
    public function add($data)
    {
        $id = M($this->tableName)->insertGetId($data);
        return $id ? $id : "";
    }

    /*
     * 修改
     * */
    public function edit($data)
    {
        return M($this->tableName)->update($data) ? 1 : 0;

    }

    /*
       * 根据id查询
       * */
    public function findById($id)
    {
        return M($this->tableName)->where("id", $id)->find();
    }

    /*
       * 根据键值对查询
       * @param  array $data 查询条件
       * */
    public function findByKV($data)
    {
        return M($this->tableName)->where($data)->order("id desc")->find();
    }

    /*
  *根据键值对批量查询
  * @param array $data 查询条件
  * */
    public function selectByKV($data)
    {
        return M($this->tableName)->where($data)->select();
    }

    /*
    * 根据条件查询分页 如:($id=>1)
    * @param int $limit |获取多少条
    * @param string $sort |排序模式 desc|asc
    * @param string $col  |需要排序的字段默认为id
    * */
    public function findByKVPage($data = "", $limit = 20, $sort = "desc", $col = "id")
    {
        if ($data) {
            $arr = M($this->tableName)->where($data)->order("$col $sort")->paginate($limit);
        } else {
            $arr = M($this->tableName)->order("$col $sort")->paginate($limit);
        }

        return $arr ? $arr : 404;
    }
    /*
    * 根据键值对条件统计条数
    * */
    public function countByKey($data="")
    {
        return is_array($data) ?  M($this->tableName)->where($data)->count() : M($this->tableName)->count() ;
    }
}
