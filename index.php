<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/1 0001
 * Time: 下午 1:52
 */
require "Mysql.class.php";

$db=new Sqli('localhost','root','root','test');
//var_dump($db->connectStatus());

$sql='select * from userinfo';
$res=$db->selectQuery($sql);
//$res=$db->selectQuery($sql,'one');
//$res=$db->selectQuery($sql,'row');
//$res=$db->selectQuery($sql,'array');
$res=$db->selectQuery($sql,'object');
//$res=$db->selectQuery($sql,'field');
echo "<pre>";
var_dump($res);