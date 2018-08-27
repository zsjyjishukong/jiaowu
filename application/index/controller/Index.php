<?php
namespace app\index\controller;
use app\code\TencentYoutuyun\Youtu;
use app\code\TencentYoutuyun\Conf;
use think\Db;
use think\facade\Session;
class Index
{
    public $jws_host = 'http://jws.hebiace.edu.cn';

    public function __construct() { //构造函数
        if (Session::get('openid')){

        } else {
            $action = request()->action();
            $this->getUserInfo($action);
        }
    }

    private function getUserInfo($action){
        if (@Session::get('openid')){
        } elseif(isset($_GET['openid'])) {
            Session::set('openid', $_GET['openid']);
            Session::set('nickname', $_GET['name']);
            Session::set('img', $_GET['img']);
        } else {
            header('Location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxce1a3e7ad3e127ef&redirect_uri=http://m.hebiace.net/app/wxGetUserInfo/getUserInfo.php?url=jws.hebiace.net/index/index/'.$action.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect');
        }
    }

    public function getJwid(){
        $openid = Session::get('openid');
        $a = Db::name('tbl_user')->where('openid',$openid)->find();
        if (isset($a['jwid'])) {
            Session::set('student_id', $a['jwid']);
            Session::set('password', $a['jwpwd']);
            return array(
                'code'=>0,
                'msg'=>'已绑定',
                'jwid'=>$a['jwid'],
                'jwpwd' => $a['jwpwd']
            );
        } else {
            return array(
                'code'=>1,
                'msg' =>'未绑定'
            );
        }
    }

    public function insertToStudent ($jwid, $jwpwd) {
        $data = ['jwid'=> $jwid, 'jwpwd'=>$jwpwd,'openid'=>Session::get('openid')];
        if (Db::name('tbl_user')
            ->data($data)
            ->insert()) {
            return json(
              array(
                  'code'=> 0,
                  'msg'=> '插入成功'
              )
            );
        } else {
            return json(
              array(
                  'code'=>1,
                  'msg'=>'插入失败'
              )
            );
        }
    }

    public function bangding(){
        return view();
    }


    public function schedule(){
        return view();
    }


    /**
     *  selectSchedule  查出课程表
     */
    public function selectSchedule($xuenian=false,$xueqi=false){
        $cookie = Session::get('cookie');
        $studentId = Session::get('student_id');
        //模拟请求抓取默认课表
        $ch=curl_init("http://jws.hebiace.edu.cn/tjkbcx.aspx?xh={$studentId}");
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_REFERER,"http://jws.hebiace.edu.cn/xs_main.aspx?xh={$studentId}");
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $str=curl_exec($ch);
        curl_close($ch);
        $str=mb_convert_encoding($str, "utf-8", "gb2312");//转换为utf-8格式
        preg_match('/selected" value="[0-9]{10,}-[0-9]{10,}">(.*[0-9]{3,4})<\/option>/u',$str,$result);//正则匹配班级
//        print_r($result);
        $banji=$result[1];
        $pattern = '/<option selected="selected" value="(.*?)">/i';
        preg_match_all($pattern,$str,$result);//正则form信息，准备进行按学期查询
        $xn = $result[1][0];
        $xq = $result[1][1];
        $nj = $result[1][2];
        $xy = $result[1][3];
        $zy = $result[1][4];
        $kb = $result[1][5];
        $pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
        preg_match($pattern, $str, $matches);
        $result['view'] = urlencode($matches[1]);
        $view = $result['view'];
        Session::set('xn',$xn);
        Session::set('xq',$xq);
        Session::set('nj',$nj);
        Session::set('xy',$xy);
        Session::set('zy',$zy);
        Session::set('kb',$kb);
        Session::set('banji',$banji);
        Session::set('view', $view);
        $code = 0;
        $error = "";
        $all = array();
        if($xuenian && $xueqi) {
            $studentId = Session::get('student_id');
            Session::set('xn', $xuenian);
            Session::set('xq', $xueqi);
            $url = $this->jws_host . "/tjkbcx.aspx?xh=$studentId";
            $refUrl = $this->jws_host . "/default2.aspx";
            $cookie = Session::get('cookie');
            $post = '__VIEWSTATE=' . $view . "&xn=" . $xuenian . "&xq=" . $xueqi . "&nj=" . $nj . "&xy=" . $xy . "&zy=" . $zy . "&kb=" . $kb;
            //模拟请求抓取特定学年学期课表
            $header = array(
                'Content-Type: application/x-www-form-urlencoded',
                'Host: jws.hebiace.edu.cn',
                'Origin: http://jws.hebiace.edu.cn'
            );
            $str = $this->curl_post($url, $cookie, $refUrl, $post, $header);
            $str = $str['str'];
            $isError = preg_match("/alert\('(.*?)'\);/iU", $str, $error);
            if ($isError) {
                $code = 1;
                $error = $error[1];
            }
        }
        if($code === 0) {
            $str = $this->getShort($str);//对过长的一些信息简化
            $all = $this->getKbArray($str);
        }

        $returnData = array(
            'code'=>$code,
            'msg'=>$error,
            'data'=>array(
                'xn'=>Session::get('xn'),
                'xq'=>Session::get('xq'),
                'bj'=>Session::get('banji'),
                'data'=>$all
            )
        );
       return json(
            $returnData
        );
    }

    private function getKbArray($table)
    {
        $table = preg_replace("/<table[^>]*?>/is", "", $table);
        $table = preg_replace("/<tr[^>]*?>/si", "", $table);
        $table = preg_replace("/<td[^>]*?>/si", "", $table);
        $table = str_replace("</tr>", "{tr}", $table);
        $table = str_replace("</td>", "{td}", $table);
        //$table = str_replace("<br><br>", "\n\n", $table);
        //$table = str_replace("<br>", "\n", $table);
        $table = str_replace("<br><br><br>", "<br/><br/>", $table);
        $table = str_replace("<br>", "<br/>", $table);
        //去掉 HTML 标记

        //去掉空白字符
        $table = preg_replace("'([rn])[s]+'", "", $table);
        $table = str_replace(" ", "", $table);
        $table = str_replace(" ", "", $table);
        $table = str_replace("&nbsp;", "", $table);

        $table = explode('{tr}', $table);
        array_pop($table);//PHP开源代码
        foreach ($table as $key => $tr) {
            $td = explode('{td}', $tr);
            $td = explode('{td}', $tr);
            array_pop($td);
            $td_array[] = $td;
        }
        return $td_array;
    }

    //减少大学体育、建筑设计,水彩这种多个老师重复出现的，导致页面被拉长的问题
    private function getShort($longContent){
        $pattern[0] = '/体育(.*?)<br>(.*?)<br>(.*?)<\/td>/i';
        $replace[0]='体育${1}<br>${2}<br></td>';
        //$pattern[4] = '/体育(.*?)<br>(.*?)<br>(.*?)<\/td>/i';

        //1 课程  2 上课时间 3 老师 4 教室
        $pattern[1] = '/>(美术.*?|建筑设计.*?|规划设计.*?|工程设计.*?|中国特色社会.*)<br>(.*?)<br>(.*?)<br>(.*?)<br><br><br>(.*?)<br><\/td>/i';
        $replace[1]= '>${1}<br>${2}<br>${4}<br></td>';

        $pattern[2] ='/毛泽东思想和中国特色社会主义理论体系概论(.*?)<br>/i';
        $replace[2]='毛概${1}<br>';

        $pattern[3]='/\([1-9]+,[1-9]+\)/i';//匹配第几节课，(7,8) 移除显示
        $replace[3]='';

        $format_content = preg_replace($pattern,$replace, $longContent);
        return $format_content;
    }

    public function getScore(){
        $cookie = Session::get('cookie');
        echo $cookie;
    }

    /**
     * @param $srcFile 需要转换的图的原地址
     * @param $dstFile 需要转换到的地址和文件名
     * @param $towidth 转换后的图片宽度
     * @param $toheight 转换后的图片高度
     */
    private function ImageToJPG($srcFile,$dstFile,$towidth,$toheight)
    {
        $quality=80;
        $data = @GetImageSize($srcFile);
        switch ($data['2'])
        {
            case 1:
                $im = imagecreatefromgif($srcFile);
                break;
            case 2:
                $im = imagecreatefromjpeg($srcFile);
                break;
            case 3:
                $im = imagecreatefrompng($srcFile);
                break;
            case 6:
                $im = ImageCreateFromBMP( $srcFile );
                break;
        }
        $srcW=@ImageSX($im);
        $srcH=@ImageSY($im);
        $dstX=$towidth;
        $dstY=$toheight;
        $ni=@imageCreateTrueColor($dstX,$dstY);
        @ImageCopyResampled($ni,$im,0,0,0,0,$dstX,$dstY,$srcW,$srcH);
        @ImageJpeg($ni,$dstFile,$quality);
        @imagedestroy($im);
        @imagedestroy($ni);
    }

    /**
     * @param string $studentId 学号
     * @param string $password 密码
     * @return \think\response\Json json编码后的状态
     */
    public function login($studentId=false, $password=false)
    {
        $code="";
        if ($studentId ===false || $password === false) {
            $studentId = Session::get('student_id');
            $password = Session::get('password');
        }
        $viewAndCookie = $this->getViewAndCookie();
        $view = $viewAndCookie['view'];
        $cookie = $viewAndCookie['cookie'];
        if ($view === null) {
            return json(
                array(
                    'code' => 1,
                    'msg' => 'view获取失败，请检查教务系统是否可用',
                    'errcode' => 1
                )
            );
        }
        if ($cookie === null) {
            return json(
                array(
                    'code' => 1,
                    'msg' => 'cookie获取失败，请检查教务系统是否可用',
                    'errcode' => 2
                )
            );
        }
        for ($errorcode=1,$i=0; $errorcode!=0 && $i<5; $i++) {
            $this->getCodeImg($cookie);
            $codeReturn = $this->getCodeNumberFromYoutu();
            $errorcode = $codeReturn['errorcode'];
            if($errorcode === 0) {
                $code = $codeReturn['items'][0]['itemstring'];
                if(strlen($code)!=4) {
                    $code = "";
                    continue;
                }
            }else{
                continue;
            }
        }
        if($code === "") {
            for($errorcode=0,$i=0; $errorcode=0&&$i<5; $i++) {
                $codeReturn=$this->getCodeNumberFromPython($studentId,$cookie);
                $errorcode = $codeReturn['status'];
                if($errorcode === false) {
                    continue;
                }else{
                    $code = $codeReturn['code'];
                }
            }
        }
        $login_url= $this->jws_host."/default2.aspx";
        $login ="__VIEWSTATE={$view}&txtUserName={$studentId}&Textbox1=&TextBox2={$password}&txtSecretCode={$code}&RadioButtonList1=%D1%A7%C9%FA&Button1=&lbLanguage=&hidPdrs=&hidsc=";
        $header = array('Origin:'.$this->jws_host);
        $jiaowu = $this->curl_post($login_url,$cookie,$login_url,$login,$header);
        $http_code = $jiaowu["http_code"];
        if($http_code === 302) {
            Session::set('cookie',$cookie);
            return json(
                array(
                    'code' => 0,
                    'msg' => '登录成功！',
                    'errcode' => 0
                )
            );
        } else {
            preg_match("/alert\('(.*?)'\);/iU", $jiaowu['str'], $error);
            return json(
                array(
                    'code' => 1,
                    'msg' => $error,
                    'errcode' => 3
                )
            );
        }
    }

    /**
     * @param $url 模拟登录的url
     * @param $cookie 需要带的cookie
     * @param bool $refereUrl 需要带的referUrl
     * @param bool $post_data 需要post的数据
     * @param $header 需要带的header
     * @return array 返回请求后的网页数据以及请求的状态码
     */
    private function curl_post($url,$cookie,$refereUrl=false,$post_data = false,$header){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($post_data){
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        }
        curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.81 Safari/537.36");
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );#ǿ�� ipv4
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        if($header){
            curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        }
        if($refereUrl){
            curl_setopt($ch, CURLOPT_REFERER, $refereUrl);
        }
        $str=curl_exec($ch);
        $str=mb_convert_encoding($str, "utf-8", "gb2312");
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $return_data = array(
            "str" => $str,
            "http_code" => $http_code,
        );
        return $return_data;
    }

    /**
     * @param string $studentId 学号
     * @return \app\code\TencentYoutuyun\返回的结果，JSON字符串，字段参见API文档
     */
    private function getCodeNumberFromYoutu(){
        $appid='10143363';
        $studentId = Session::get('student_id');
        $secretId='AKID67wb5NoZOBnDi5hVRBmPs9MOI3d1rzhc';
        $secretKey='AL05yfInEalndG6FHGGGC7BRPI2b1FHg';
        $userid='1361882279';
        $expired = time() + 60;
        $image_path = "codes/$studentId.jpg";
        $image_path = realpath($image_path);
        Conf::setAppInfo($appid, $secretId, $secretKey, $userid,conf::API_YOUTU_END_POINT);
//        Auth::appSign($expired, $userid);
        $result=YouTu::generalocr($image_path, $seq = '');
        return $result;
    }

    /**
     * @param $xuehao 学号
     * @param $cookie 登录时带的cookie
     * @return mixed
     */
    private function getCodeNumberFromPython($xuehao,$cookie){
        $url = "http://47.104.219.255:9699/getCode?xuehao={$xuehao}";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        if(!empty($cookie)){
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    /**
     * @param string $cookie login成功后的cookie
     * @param string $studentId 学号
     */
    private function getCodeImg($cookie){
        $studentId = Session::get('student_id');
        $verify_code_url = $this->jws_host."/CheckCode.aspx"; //验证码地址
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $verify_code_url);
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);  //使用cookie
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $img = curl_exec($curl);  //执行curl
        curl_close($curl);
        $fp = fopen("codes/$studentId.gif","w");  //文件名
        fwrite($fp,$img);  //写入文件
        fclose($fp);
        $fromSrc = realpath("codes/$studentId.gif");
        $toSrc = explode('.',$fromSrc);
        $toSrc = $toSrc[0].".jpg";
        $this->ImageToJPG($fromSrc, $toSrc ,72,27);
    }

    /**
     * @return array 返回数组，cookie和view
     */
    private function getViewAndCookie(){
        $result = array();
        $ch = curl_init($this->jws_host."/default2.aspx");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");       #gzip, deflate, sdch
        $str = curl_exec($ch);
        curl_close($ch);
        $pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
        preg_match($pattern, $str, $matches);
        $result['view'] = urlencode($matches[1]);
        preg_match('/Set-Cookie:(.*);/iU', $str, $matches);
        $result['cookie'] = $matches[1];
        return $result;
    }
}
