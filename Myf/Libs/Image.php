<?php
/**
 * remark
 * User: myf
 * Date: 17/3/1
 * Time: 下午3:48
 */

namespace Myf\Libs;


class Image {

    /**
     * 制作证书
     * @param $data
     * @return string
     */
    public static function makeCertificate($data) {
        $sitePath = APP_DIR;
        //1.配置图片路径
        $src = sprintf("%s/%s", $sitePath, 'template.jpg');
        //2.获取图片信息
        $info = getimagesize($src);
        //3.通过编号获取图像类型
        $type = image_type_to_extension($info[2], false);
        //4.在内存中创建和图像类型一样的图像
        $fun = "imagecreatefrom" . $type;
        //5.图片复制到内存
        $image = $fun($src);
        //6.设置字体颜色和透明度
        $color = imagecolorallocatealpha($image, 0, 0, 0, 0);
        /*操作图片*/
        //7.设置字体的路径
        $font = sprintf("%s/%s", $sitePath, "wryh.ttf");
        //写入文本
        // 授权编号
        $number = $data['number'];
        imagettftext($image, 20, 0, 468, 510, $color, $font, $number);
        //姓名
        $name = $data['name'];
        $nl = 184;
        $nameLen = mb_strlen($name);
        if ($nameLen == 2) {
            $nl = 215;
        } elseif ($nameLen == 3) {
            $nl = 184;
        } elseif ($nameLen > 3) {
            $nl = 160;
        }
        imagettftext($image, 24, 0, $nl, 606, $color, $font, $name);
        //微信账号
        $weixin = $data['weixin'];
        imagettftext($image, 20, 0, 554, 728, $color, $font, $weixin);
        //城市
        $city = $data['city_info'];
        imagettftext($image, 20, 0, 554, 785, $color, $font, $city);
        //身份证
        $idNumber = $data['id_number'];
        $idNumber = substr($idNumber, 0, 6) . "********" . substr($idNumber, -4);
        imagettftext($image, 20, 0, 554, 840, $color, $font, $idNumber);
        //过期时间
        $expTime = date("Y年m月d日", strtotime($data['exp_time']));
        imagettftext($image, 20, 0, 382, 1108, $color, $font, $expTime);
        //开始时间
        $startTime = date("Y年m月d日", strtotime($data['start_time']));
        imagettftext($image, 20, 0, 682, 1220, $color, $font, $startTime);
        //级别
        $level = $data['level_name'];
        imagettftext($image, 24, 0, 430, 607, $color, $font, $level);
        //头像路径
        $wh = 132;//头像高和宽
        $openid = $data['openid'];
        $avatarFile = sprintf("%s/upload/avatar/%s_%s.jpeg", $sitePath, $openid, $wh);
        $src = imagecreatefromjpeg($avatarFile);
        imagecopymerge($image, $src, 234, 707, 0, 0, $wh, $wh, 100);
        $outFile = sprintf("%s/upload/certificate/%s.jpg", $sitePath, $openid);
        Utils::createFolders(dirname($outFile));
        if (is_file($outFile)) {
            unlink($outFile);
        }
        imagejpeg($image, $outFile);
        /*销毁图片*/
        imagedestroy($image);
        imagedestroy($src);
        return sprintf('upload/certificate/%s.jpg', $openid);
    }

    public static function downImage($url, $fileName) {

        $path = APP_DIR;
        $fileName = sprintf("%s/%s",$path,$fileName);
        if(is_file($fileName)){
            return true;
        }
        createFolders(dirname($fileName));
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        ob_start ();
        curl_exec ( $ch );
        $return_content = ob_get_contents ();
        ob_end_clean ();
        curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
        $fp = @fopen($fileName, "a"); //将文件绑定到流
        fwrite($fp, $return_content); //写入文件

        return true;
    }

}