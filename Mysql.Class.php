<?php

class Mysql
{
    private $localhost;//数据库host
    private $username;//数据库用户名
    private $password;//用户密码
    private $charset;//数据库连接charset
    private $dbname;//数据库库名
    private $connectResource;//数据库连接资源;
    private $connectErrorArr;//数据库连接错误信息;
    private $ret = 'nishisb';
    private $res = 'nicaishisb';

    public function __construct($localhost, $username, $password, $dbname = '', $charset = 'utf8')
    {
        $this->localhost = $localhost;
        $this->username = $username;
        $this->password = $password;
        $this->charset = $charset;

        //尝试连接数据库;
        $connectResource = @mysqli_connect($this->localhost, $this->username, $this->password);
        //不成功则保存错误值;
        if (!$connectResource) {
            $this->connectErrorArr = $this->mysqliConnectError();
        } else {
            $this->connectResource = $connectResource;
        }
        if ($dbname) {
            $this->errorArr = $this->chooseDb($dbname);
        }
    }

    //选择数据库;
    public function chooseDb($db)
    {
        if ($db && preg_match('/^[a-zA-Z][\w_]*[\w]$/', $db)) {
            $this->dbname = $db;
            $res = mysqli_select_db($this->connectResource, $db);
            if ($res) {
                return true;
            } else {
                return $this->mysqliError();
            }
        }
    }


    //mysqli报错信息数组;
    private function mysqliConnectError()
    {
        $arr = array();
        $arr['errno'] = mysqli_connect_errno();
        $arr['msg'] = mysqli_connect_error();
        return $arr;
    }

    public function showMysqliError()
    {
        return $this->connectErrorArr;
    }


    public function selectQuery($sql, $type = 'assoc')
    {
        $sql = ltrim($sql);
        if ($sql && preg_match('/^select|SELECT\s+[\w|\*]+/', $sql)) {
            $this->ret = mysqli_query($this->connectResource, $sql);
            $res = array();
            switch ($type) {
                default:
                case 'assoc':
                    while ($this->ret && $tmp = mysqli_fetch_assoc($this->ret)) {
                        $res[] = $tmp;
                    }
                    break;
                case 'array':
                    while ($this->ret && $tmp = mysqli_fetch_array($this->ret)) {
                        $res[] = $tmp;
                    }
                    break;
                case 'all':
                    if ($this->ret && $tmp = mysqli_fetch_all($this->ret)) {
                        $res = $tmp;
                    }
                    break;
//                    查询字段信息;
                case 'field':
                    while ($this->ret && $tmp = mysqli_fetch_field($this->ret)) {
                        $res[] = $tmp;
                    }
                    break;
//                    以对象形式输出;
                case 'object':
                    while ($this->ret && $tmp = mysqli_fetch_object($this->ret)) {
                        $res[] = $tmp;
                    }
                    break;
                    //查询单行;
                case 'row':
                    if ($this->ret && $tmp = mysqli_fetch_row($this->ret)) {
                        $res = $tmp;
                    }
                    break;
                    //查询单个(第一行第一个)
                case 'one':
                    if ($this->ret && $tmp = mysqli_fetch_row($this->ret)) {
                        $res = array_shift($tmp);
                    }
                    break;
            }

            if (!empty($res)) {
                $this->res = $res;
                return $this->res;
            } else {
                return mysqli_error_list($this->connectResource);
            }
        } else {
            return array('error' => 'key must be select');
        }
    }

    //简易执行新增/修改/删除语句;
    public function executeQuery($sql)
    {
        $sql = ltrim($sql);
        if ($sql && preg_match('/^(insert into )|(INSERT INTO)|(update )|(UPDATE)\w+/', $sql)) {
            $this->ret = mysqli_query($this->connectResource, $sql);
            if ($this->ret && $affected_rows = mysqli_affected_rows($this->connectResource)) {
                $this->res = array();
                $this->res['affected_rows'] = $affected_rows;
                $this->res['insert_id'] = mysqli_insert_id($this->connectResource);
                return $this->res;
            } else {
                return mysqli_error_list($this->connectResource);
            }
        } else if ($sql && preg_match('/^(delete from)|(DELETE FROM) \w+/', $sql)) {
            $this->ret = mysqli_query($this->connectResource, $sql);
            if ($this->ret && $affected_rows = mysqli_affected_rows($this->connectResorce)) {
                $this->res = array();
                $this->res['affected_rows'] = $affected_rows;
                return $this->res;
            } else {
                return mysqli_error_list($this->connectResource);
            }
        }else{
            return array('error' => 'KEY MUST BE ONE OF THE WORDS: insert into OR update OR delete from');
        }
    }


    /*数组执行insert/update/delete 语句
     * 数组规格:
     * array(
     * 'field'=>'id,name,age',
     * 'from'=>'userinfo',
     * 'value'=>'1,jack,12',
     * 'where'=>'id>12'
     * );
     * 执行结果:成功返回影响行数,失败返回mysqli_error_list($this->connectResource);
     * */
    public function insertQuery($arr){

    }
    public function updateQuery($arr){

    }
    public function deleteQuery($arr){

    }
    //正则验证sql语句是否正确;
    /*正则验证sql语句是否正确;
     * 正确返回已修正的sql(去除头尾空格,修改字段符号为``,全大写关键字)
     * 否则返回错误数组array('error'=>'INCURRECT SQL KEY');
     * */
    private function sqlToLowerString($sql){
        $sql_=ltrim($sql);
        $key=strtolower(substr($sql_,0,6));
        switch($key){
            case 'select':{}break;
            case 'insert':{}break;
            case 'update':{}break;
            case 'delete':{}break;
            default:{}break;
        }

    }
    //不安全
    /*public function getValue($x)
    {
        if(isset($this->$x)){
            return $this->$x;
        }

    }*/

}