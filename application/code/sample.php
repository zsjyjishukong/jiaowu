<?php

require('./include.php');
use TencentYoutuyun\Youtu;
use TencentYoutuyun\Conf;


// 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用

$appid='10143363';
$secretId='AKID67wb5NoZOBnDi5hVRBmPs9MOI3d1rzhc';
$secretKey='AL05yfInEalndG6FHGGGC7BRPI2b1FHg';
$userid='1361882279';


Conf::setAppInfo($appid, $secretId, $secretKey, $userid,conf::API_YOUTU_END_POINT );


// 人脸检测 调用列子
//$uploadRet = YouTu::detectface('a.jpg', 1);
//var_dump($uploadRet);


// 人脸定位 调用demo
//$uploadRet = YouTu::faceshape('a.jpg', 1);
//var_dump($uploadRet);

//黄图识别
//$uploadRet = YouTu::imageporn('test.jpg', 1);
//var_dump($uploadRet);
//$uploadRet = YouTu::imagepornurl('http://open.youtu.qq.com/content/img/product/face/face_05.jpg', 1);
//var_dump($uploadRet);

//身份证ocr

//$uploadRet = YouTu::idcardocr('test.jpg', 1);
//var_dump($uploadRet);
//$uploadRet = YouTu::idcardocrurl('http://open.youtu.qq.com/content/img/product/face/face_05.jpg', 1);
//var_dump($uploadRet);

//名片
// $uploadRet = YouTu::namecardocr('test.jpg', 1);
// var_dump($uploadRet);
$uploadRet = YouTu::namecardocrurl('http://open.youtu.qq.com/content/img/product/face/face_05.jpg', 1);
var_dump($uploadRet);

// $uploadRet = YouTu::plateocr('plate.jpg');
// var_dump($uploadRet);
$uploadRet = YouTu::plateocrurl('http://open.youtu.qq.com/app/img/experience/char_general/ocr_license_1.jpg');
var_dump($uploadRet);

// $uploadRet = YouTu::bizlicenseocr('biz.jpg');
// var_dump($uploadRet);
$uploadRet = YouTu::bizlicenseocrurl('http://open.youtu.qq.com/app/img/experience/char_general/ocr_yyzz_01.jpg');
var_dump($uploadRet);

// $uploadRet = YouTu::creditcardocr('credit.jpg');
// var_dump($uploadRet);

$uploadRet = YouTu::creditcardocrurl('http://open.youtu.qq.com/app/img/experience/char_general/ocr_card_1.jpg');
var_dump($uploadRet);

// $uploadRet = YouTu::carclassify('car.jpg');
// var_dump($uploadRet);

$uploadRet = YouTu::carclassifyurl('http://open.youtu.qq.com/app/img/experience/car/car_01.jpg');
var_dump($uploadRet);

// $uploadRet = YouTu::imageterrorism('terror.jpg');
// var_dump($uploadRet);

$uploadRet = YouTu::imageterrorismurl('http://open.youtu.qq.com/app/img/experience/terror/img_terror01.jpg');
var_dump($uploadRet);

$group_ids = array();
// $uploadRet = YouTu::multifaceidentify('terror.jpg', "test", $group_ids, 5, 40);
// var_dump($uploadRet);

$uploadRet = YouTu::multifaceidentifyurl('http://open.youtu.qq.com/app/img/experience/face_img/face_13.jpg', "test", $group_ids, 5, 40);
var_dump($uploadRet);

?>
