<?php

class Sqli
{
    private $localhost;//数据库host
    private $username;//数据库用户名
    private $password;//用户密码
    private $charset;//数据库连接charset
    private $dbname;//数据库库名
    private $connectResource;//数据库连接资源;
    private $connectErrorArr;//数据库连接错误信息;
    private $ret;//返回的资源变量ret(urn);
    private $res;//根据资源变量得出的结果res(ult)

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
            $this->chooseDb($dbname);
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
                return $this->showMysqliError();
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

    private function showMysqliError()
    {
        return $this->connectErrorArr;
    }

    //查询;
    public function selectQuery($sql, $type = 'assoc')
    {
        $sqlArr = $this->checkSql($sql);
        if (!$sqlArr['error'] && $sqlArr['type']==='select') {
            $this->ret = mysqli_query($this->connectResource, $sqlArr['sql']);
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
                mysqli_free_result($this->ret);
                return $this->res;
            } else {
                return mysqli_error_list($this->connectResource);
            }
        } else {
            return $sqlArr['error'];
        }
    }

    //简易执行新增/修改/删除语句;
    public function executeQuery($sql)
    {
        $sqlArr = $this->checkSql($sql);
        if (!$sqlArr['error'] && $sqlArr['type']==='insert'||$sqlArr['type']==='update') {
            $this->ret = mysqli_query($this->connectResource, $sqlArr['sql']);
            if ($this->ret && $affected_rows = mysqli_affected_rows($this->connectResource)) {
                $this->res = array();
                $this->res['affected_rows'] = $affected_rows;
                $this->res['insert_id'] = mysqli_insert_id($this->connectResource);
                return $this->res;
            } else {
                return mysqli_error_list($this->connectResource);
            }
        } else if (!$sqlArr['error'] && $sqlArr['type']==='delete') {
            $this->ret = mysqli_query($this->connectResource, $sqlArr['sql']);
            if ($this->ret && $affected_rows = mysqli_affected_rows($this->connectResource)) {
                $this->res = array();
                $this->res['affected_rows'] = $affected_rows;
                return $this->res;
            } else {
                return mysqli_error_list($this->connectResource);
            }
        } else {
            return $sqlArr['error'];
        }
    }


    /*数组执行insert/update/delete 语句
     * 数组规格:
     * array(
     * 'field'=>'id,name,age',
     * 'from'=>'userinfo',
     * 'value'=>'1,jack,12',
     * //或者数组
     * 'value'=>array(
     *      '1,jack,12','2,lucy,13','3,jinx,12'....
     *      ),
     * //
     * 'where'=>'id>"12"'
     * );
     * 执行结果:成功返回影响行数,失败返回mysqli_error_list($this->connectResource);
     * */
    public function insertQuery($arr)
    {
        $field=$this->strToSql($arr['field'],"`");
        if(is_array($arr['value'])){
            //是数组,拼接数组;
            $values="";
            foreach($arr['value'] as $k => $v){
                $values.=$this->strToSql($v,"'")."),(";
            }
            $values=rtrim($values,"),(");
        }else {
            //不是数组,直接拼接;
            $values = $this->strToSql($arr['value'], "'");
        }

        $tmpsql="INSERT INTO ".$arr['from']." (".$field.") VALUES (".$values.")";
//        var_dump($tmpsql);
//        return $tmpsql;

        //执行sql;
        $this->ret = mysqli_query($this->connectResource, $tmpsql);
        if ($this->ret && $affected_rows = mysqli_affected_rows($this->connectResource)) {
            $this->res = array();
            $this->res['affected_rows'] = $affected_rows;
            $this->res['insert_id'] = mysqli_insert_id($this->connectResource);
            return $this->res;
        } else {
            return mysqli_error_list($this->connectResource);
        }

    }

    public function updateQuery($arr)
    {

    }

    public function deleteQuery($arr)
    {

    }
    //正则验证sql语句是否正确;
    /*正则验证sql语句是否正确;
     * 正确返回已修正的sql(去除头尾空格);
     * 否则返回错误数组array('error'=>'INCURRECT SQL KEY');
     * */
    private function checkSql($sql)
    {
        $sql_ = trim($sql);
        $key = strtolower(substr($sql_, 0, 6));
        switch ($key) {
            case 'select':
            case 'insert':
            case 'update':
            case 'delete': {
                return array('sql'=>$sql_,'type'=>$key,'error'=>false);
            }
                break;
            default: {
                return array('error'=>'INCURRECT SQL KEY');
            }
                break;
        }
    }
    /* 字符串转换成sql需要的格式: 加上``或者'' */
    private function strToSql($str,$separator="'"){
        //把  id,name,age,like  转换成  `id`,`name`,`age`,`like`
        //把  1,jack,12,eat     转换成  '1','jack','12','eat'
        if(strlen($str)>0) {
            $result = "";
            $tmp = explode(",", $str);
            foreach ($tmp as $key => $value) {
                $result .= $separator.$value.$separator.",";
            }
            $result = rtrim($result, ",");
            return $result;
        }else{
            return false;
        }
    }
    //不安全
    public function getValue($x)
    {
        if(isset($this->$x)){
            return $this->$x;
        }
    }
    public function connectStatus(){
        if(count($this->connectErrorArr)===0){
            return $this->connectResource;
        }else{
            return $this->connectErrorArr;
        }
    }

}