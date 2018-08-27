# TencentYoutuyun-person-face-service

php sdk for [腾讯优图开放平台](http://open.youtu.qq.com)

## 安装（直接下载源码集成）


从github下载源码装入到您的程序中，并加载include.php

## 名词

	- `AppId` 平台添加应用后分配的AppId
	- `SecretId` 平台添加应用后分配的SecretId
	- `SecretKey` 平台添加应用后分配的SecretKey
	- `签名` 接口鉴权凭证，由`AppId`、`SecretId`、`SecretKey`等生成，详见<http://open.youtu.qq.com/#/develop/tool-authentication>



## 使用示例

##### 引入SDK  
```
require('./include.php');     
use TencentYoutuyun\Youtu;    
use TencentYoutuyun\Conf;      
use TencentYoutuyun\Auth;     
```
##### 设置APP 鉴权信息  
```
$appid='your appid';     
$secretId='your secretId ';     
$secretKey='your secretKey';      
$userid='your qq';  
```

##### 根据你使用的平台选择一种初始化方式


* 优图开放平台初始化   
```
Conf::setAppInfo($appid, $secretId, $secretKey, $userid,conf::API_YOUTU_END_POINT);
```
* 优图开放平台核身服务初始化（**核身服务目前仅支持核身专有接口,需要联系商务开通**）    
```
Conf::setAppInfo($appid, $secretId, $secretKey, $userid,conf::API_YOUTU_CHARGE_END_POINT);
```
* 腾讯云初始化     
```
Conf::setAppInfo($appid, $secretId, $secretKey, $userid,conf::API_TENCENTYUN_END_POINT);
```
* 人脸检测接口调用示例  
```
$uploadRet = YouTu::detectface('test.jpg', 1);  
var_dump($uploadRet);
```



### `鉴权`

接口调用时 计算签名鉴权相关逻辑。

* `Auth::appSign($expired, $userid)`

	获取签名，依赖`conf`中的配置项。

	- 参数
    	- `expired` 过期时间，UNIX时间戳, 计算的签名在过期时间之前有效.
    	- `userid` 业务中的用户标识，填写用户QQ号即可
- 返回值： 签名

- 其它
	
	- `auth.AUTH_PARAMS_ERROR` 参数错误常量（-1）
	- `auth.AUTH_SECRET_ID_KEY_ERROR` 密钥ID或者密钥KEY错误常量（-2）

     
***      
#####  Api分为开放平台API和核身API，**核身API访问权限需要联系商务开通**；开放平台API访问域名为https://api.youtu.qq.com/， 核身API访问域名为https://vip-api.youtu.qq.com/
***

### `核身API介绍`
优图开放平台相关核身API封装，均为同步函数，**需要联系商务开通**
***
* `YouTu::livegetfour() `

	获取四字唇语

	- 参数
    	- `$image_path` 无


* `YouTu::livedetectfour($validate_data,$video_path,$card_path,$compare_card=false,$seq = '') `

	带数据源四字人脸核身

	- 参数
    	- `$validate_data`  livegetfour获取的四字唇语
    	- `$video_path`  视频的路径
    	- `$card_path`  对比照片的路径
    	- `$compare_card`  视频与照片是否进行对比，true 对比 false不对比


* `YouTu::idcardlivedetectfour($idcard_number,$idcard_name,$validate_data,$video_path,$seq = '') `

	不带数据源四字人脸核身

	- 参数
		- `$idcard_number`  身份证号码
    	- `$idcard_name`  身份证姓名
    	- `$validate_data`  livegetfour获取的四字唇语
    	- `$video_path`  视频的路径



* `YouTu::idcardfacecompare($idcard_number,$idcard_name,$image_path,$seq = '') `

	不带数据源人脸对比

	- 参数
		- `$idcard_number`  身份证号码
    	- `$idcard_name`  身份证姓名
    	- `$image_path`  照片的路径


* `YouTu::validateidcard($idcard_number,$idcard_name,$seq = '') `

	验证身份证信息的有效性

	- 参数
		- `$idcard_number`  身份证号码
    	- `$idcard_name`  身份证姓名


* `YouTu::idcardocr($image_path,  $card_type, $seq = '') `
* `YouTu::idcardocrurl($url,  $card_type, $seq = '') `

	身份证OCR识别

	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url
    	- `$card_type` 0 代表输入图像是身份证正面， 1代表输入是身份证反面


* `YouTu::facecompare($image_path_a, $image_path_b)`
* `YouTu::facecompareurl($urlA, $urlB)`

	人脸对比，计算两个Face的相似性以及五官相似度。

	- 参数
		- `$image_path_a` 第一张待检测图片路径
		- `$image_path_b` 第二张待检测图片路径
		- `$urlA` 第一张图片url
		- `$urlB` 第二张图片url


***
### `开放平台API介绍`

优图开放平台相关API封装，均为同步函数

****

* `YouTu::detectface($image_path, $isbigface)`
* `YouTu::detectfaceurl($url, $isbigface)`

	人脸检测，检测给定图片(Image)中的所有人脸(Face)的位置和相应的面部属性。位置包括(x, y, w, h)，面部属性包括性别(gender)、年龄(age)
	表情(expression)、眼镜(glass)和姿态(pitch，roll，yaw)。

	- 参数
		- `$image_path` 待检测图片路径
		- `$url` 待检测图片的url
		- `$isbigface` 是否大脸模式 ０表示检测所有人脸， 1表示只检测照片最大人脸　适合单人照模式



* `YouTu::faceshape($image_path, $isbigface)`
* `YouTu::faceshapeurl($url, $isbigface)`

	人脸定位，检测给定图片中人脸的五官。对请求图片进行人脸配准，计算构成人脸轮廓的88个点，
	包括眉毛（左右各8点）、眼睛（左右各8点）、鼻子（13点）、嘴巴（22点）、脸型轮廓（21点）

	- 参数
		- `$image_path` 待检测图片路径
		- `$url` 待检测图片的url
		- `$isbigface` 是否大脸模式 ０表示检测所有人脸， 1表示只检测照片最大人脸　适合单人照模式


* `YouTu::facecompare($image_path_a, $image_path_b)`
* `YouTu::facecompareurl($urlA, $urlB)`

	人脸对比，计算两个Face的相似性以及五官相似度。

	- 参数
		- `$image_path_a` 第一张待检测图片路径
		- `$image_path_b` 第二张待检测图片路径
		- `$urlA` 第一张图片url
		- `$urlB` 第二张图片url


* `YouTu::faceverify($image_path, $person_id)`
* `YouTu::faceverifyurl($url,$person_id)`

	人脸验证，给定一个Face和一个Person，返回是否是同一个人的判断以及置信度。

	- 参数
		- `$image_path` 待检测图片路径
		- `$url` 待检测图片的url
		- `$person_id` 待验证的Person


* `YouTu::faceidentify($image_path, $group_id)`
* `YouTu::faceidentifyurl($url,$group_id)`
人脸识别，对于一个待识别的人脸图片，在一个Group中识别出最相似的Top5 Person作为其身份返回，返回的Top5中按照相似度从大到小排列。

	- 参数
		- `$image_path` 待检测图片路径
		- `$url` 待检测图片的url
		- `$group_id` 需要识别的人 所在的组

* `YouTu::multifaceidentify($image_path,$group_id, array $group_ids, $topn=5, $min_size=40)`
* `YouTu::multifaceidentifyurl($url, $group_id, array $group_ids, $topn=5, $min_size=40)`
上传人脸图片，进行多人脸检索。
	- 参数
		- `$image_path` 待检测图片路径
		- `$url` 待检测图片的url
		- `$group_id` 需要识别的人 所在的组
		- `$group_ids` 需要识别的人 所在的组的列表（数组）
		- `$topn` 候选人脸数量，一般使用默认值5
		- `$min_size` 人脸检测最小尺寸，一般使用默认值40


* `YouTu::newperson($image_path, $person_id, array $group_ids, $person_name="", $person_tag="")`
* `YouTu::newpersonurl($url, $person_id, array $group_ids, $person_name="", $person_tag="")`

	个体创建，创建一个Person，并将Person放置到$group_ids指定的组当中。

	- 参数
		- `$image_path` 待检测图片路径
		- `$url` 待检测图片的url
		- `$person_id` 个体id
		- `$person_name` 个体的名字
		- `$group_ids` 要加入的组的列表（数组）
		- `$person_name` 个体名称
		- `$person_tag` 备注信息，用户自解释字段

* `YouTu::delperson($person_id)`

	删除一个Person

	- 参数
		- `$person_id` 个体Person


* `YouTu::addface($person_id, $images, $facetag)`
* `YouTu::addfaceurl($person_id, $url_arr, $facetag="")`

	添加人脸，在创建一个Person后， 增加person下面的人脸, 可以用于后面的比对。

	- 参数
		- `$person_id` 个体Person
		- `$images` 待检测图片路径(数组)
		- `$url_arr` 图片url(数组)
		- `$facetag` 人脸自定义标签


* `YouTu::delface($person_id, $face_ids)`

	删除人脸，删除一个person下的face，包括特征，属性和face_id。

	- 参数
		- `$person_id` 个体Person
		- `$face_ids` 要删除的faceId列表（数组）

* `YouTu::setinfo($person_name, $person_id, $tag)`

	设置Person的信息

	- 参数
		- `$person_name` 个体Person的name
		- `$person_id` 个体Person
		- `$tag` 个体Person的tag, 用户自解释字段

* `YouTu::getinfo($person_id)`

	获取一个Person的信息，包括name、id、$tag、相关的face以及groups等信息。

	- 参数
		- `$person_id` 个体Person

* `YouTu::getgroupids()`

	获取一个AppId下所有group列表

	- 返回值
		- 返回的结果，JSON字符串，字段参见API文档

* `YouTu::getpersonIds($group_id)`

	获取一个组Group中所有person列表

	- 参数
		- `$group_id` 组

* `YouTu::getfaceIds($person_id)`
	
	获取一个组person中所有face列表

	- 参数
		- `$person_id` 个体Person

* `YouTu::getfaceinfo($face_id)`
	
	获取一个face的相关特征信息

	- 参数
		- `$face_id` 需要获取的faceid


* `YouTu::fuzzydetect($image_path)`
* `YouTu::fuzzydetecturl($url)`

	判断一个图像的模糊程度

	- 参数
		- `$image_path` 待检测图片路径
		- `$url` 待检测图片的url


* `YouTu::fooddetect($image_path)`
* `YouTu::fooddetecturl($url)`
	
	识别一个图像是否为美食图像

	- 参数
		- `$image_path` 待检测图片路径
		- `$url` 待检测图片的url


* `YouTu::imagetag($image_path)`
* `YouTu::imagetagurl($url)`

	识别一个图像的标签信息,对图像分类

	- 参数
		- `$image_path` 待检测图片路径
		- `$url` 待检测图片的url

* `YouTu::imageporn($image_path)`
* `YouTu::imagepornurl($url)`

	色情图像检测
	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url

* `YouTu::imageterrorism($image_path)`
* `YouTu::imageterrorismurl($url)`

	暴恐图片识别
	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url

* `YouTu::carclassify($image_path)`
* `YouTu::carclassifyurl($url)`

	车辆属性识别
	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url

* `YouTu::idcardocr($image_path,  $card_type, $seq = '') `
* `YouTu::idcardocrurl($url,  $card_type, $seq = '') `

	身份证ocr识别

	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url
    	- `$retimage` 0代表不需要返回识别后图像， 1代表需要返回识别后图像


* `YouTu::generalocr($image_path, $seq = '') `
* `YouTu::generalocrurl($url, $seq = '') `

	通用OCR识别

	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url


* `YouTu::bcocr($image_path, $seq = '') `
* `YouTu::bcocrurl($url, $seq = '') `

	名片OCR识别

	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url


* `YouTu::driverlicenseocr($image_path,  $card_type, $seq = '') `
* `YouTu::driverlicenseocrurl($url,  $card_type, $seq = '') `

	行驶证&驾驶证OCR识别

	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url
    	- `$card_type` 0 代表输入图像是行驶证， 1代表输入图像是驾驶证

* `YouTu::plateocr($image_path, $seq = '') `
* `YouTu::plateocrurl($url, $seq = '') `

	车牌OCR识别

	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url

* `YouTu::creditcardocr($image_path, $seq = '') `
* `YouTu::creditcardocrurl($url, $seq = '') `

	银行卡OCR识别

	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url

* `YouTu::bizlicenseocr($image_path, $seq = '') `
* `YouTu::bizlicenseocrurl($url, $seq = '') `

	营业执照OCR识别

	- 参数
    	- `$image_path` 待检测图片路径
    	- `$url`待检测图片的url

####更多详情和文档说明参见
* [腾讯优图开放平台](http://open.youtu.qq.com)

