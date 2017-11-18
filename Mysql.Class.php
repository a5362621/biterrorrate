<?php

class Mysql
{
    private $localhost;
    private $username;
    private $password;
    private $charset;
    private $dbname;
    private $connectResource;//数据库连接资源
    private $errorArr;//错误数组

    public function __construct($localhost, $username, $password, $charset = 'utf8', $dbname = '')
    {
        $this->localhost = $localhost;
        $this->username = $username;
        $this->password = $password;
        $this->charset = $charset;

        //尝试连接数据库;
        $connectResource = mysqli_connect($this->localhost, $this->username, $this->password);
        //不成功则保存错误值;
        if (!$connectResource) {
            $this->errorArr = $this->connectError();
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
    private function connectError()
    {
        $arr = array();
        $arr['errno'] = mysqli_connect_errno();
        $arr['msg'] = mysqli_connect_error();
        return $arr;
    }

    public function showMysqliError()
    {
//        return 'helloworld';
//        return $this->connectResource;
        return $this->errorArr;
    }

    public function getValue()
    {
        return $this->localhost;
    }

}