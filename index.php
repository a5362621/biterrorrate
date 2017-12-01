<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/1 0001
 * Time: 下午 1:52
 */
require "Sqli.class.php";

$db=new Sqli('localhost','root','root','test');
//var_dump($db->connectStatus());

//$sql='select * from userinfo';
//$res=$db->selectQuery($sql);
//$res=$db->selectQuery($sql,'one');
//$res=$db->selectQuery($sql,'row');
//$res=$db->selectQuery($sql,'array');
//$res=$db->selectQuery($sql,'all');
//$res=$db->selectQuery($sql,'object');
//$res=$db->selectQuery($sql,'field');


$sql='insert into userinfo (`name`,`age`,`like`) values ("jax","15","fight"),("linux","33","command")';
//$res=$db->executeQuery($sql);


//$arr=array(
//    'tbname'=>'userinfo',
//    'field'=>'name,age,like',
//    'value'=>'max,15,football'
//);
$arr=array(
    'tbname'=>'userinfo',
    'field'=>'name,age,like',
    'value'=>array('wamp,21,lady','lnmp,21,lady','mnmp,21,lady')
);
$res=$db->insertQuery($arr);

echo "<pre>";
print_r($res);