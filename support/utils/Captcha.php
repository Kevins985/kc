<?php

namespace support\utils;

class Captcha {

    private $width;
    private $height;
    private $codeNum;
    private $image;   //图像资源
    private $disturbColorNum;
    private $checkCode;

    function __construct($width=60, $height=22, $codeNum=5) {
        $this->width = $width;
        $this->height = $height;
        $this->codeNum = $codeNum;
        $this->checkCode = $this->createCheckCode();
        $number = floor($width * $height / 15);
        if ($number > 240 - $codeNum) {
            $this->disturbColorNum = 240 - $codeNum;
        } else {
            $this->disturbColorNum = $number;
        }
    }

    //通过访问该方法向浏览器中输出图像
    function showImage($fontFace="") {
        //第一步：创建图像背景
        $this->createImage();
        //第二步：设置干扰元素
        $this->setDisturbColor();
        //第三步：向图像中随机画出文本
        $this->outputText($fontFace);
        //第四步：输出图像
        $this->outputImage();
    }

    //通过调用该方法获取随机创建的验证码字符串
    function getCheckCode() {
        return strtolower($this->checkCode);
    }

    private function createImage() {
        //创建图像资源
        $this->image = imagecreatetruecolor($this->width, $this->height);
        //随机背景色
        $backColor = imagecolorallocate($this->image, rand(225, 255), rand(225, 255), rand(225, 255));
//        $backColor=imagecolorallocate($this->image,255,255,255);
        //为背景添充颜色
        imagefill($this->image, 0, 0, $backColor);
        //设置边框颜色
//        $border = imagecolorallocate($this->image, 0, 0, 0);
        //画出矩形边框
//        imagerectangle($this->image, 0, 0, $this->width - 1, $this->height - 1, $border);
    }

    private function setDisturbColor() {
        for ($i = 0; $i < $this->disturbColorNum; $i++) {
            $color = imagecolorallocate($this->image, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($this->image, rand(1, $this->width - 2), rand(1, $this->height - 2), $color);
        }

        for ($i = 0; $i < 10; $i++) {
            $color = imagecolorallocate($this->image, rand(200, 255), rand(200, 255), rand(200, 255));
            imagearc($this->image, rand(-10, $this->width), rand(-10, $this->height), rand(30, 300), rand(20, 200), 55, 44, $color);
        }
    }

    private function createCheckCode() {
            $code = "123456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKMNPQRSTUVWXYZ";
            $string = '';
            for ($i = 0; $i < $this->codeNum; $i++) {
                $char = $code[rand(0, strlen($code) - 1)];
                $string.=$char;
            }
            return $string;
    }

    private function outputText($fontFace="") {
        for ($i = 0; $i < $this->codeNum; $i++) {
            $fontcolor = imagecolorallocate($this->image, rand(0, 128), rand(0, 128), rand(0, 128));
            if ($fontFace == "") {
                $fontsize = rand(16, 20);
                $x = floor($this->width / $this->codeNum) * $i+ 3;
                $y = rand(0, $this->height - 15);
                imagechar($this->image, $fontsize, $x, $y, $this->checkCode[$i], $fontcolor);
            } else {
                $fontsize = rand(16, 20);
                $x = floor(($this->width - 8) / $this->codeNum) * $i + 8;
                $y = rand($fontsize + 5, $this->height);
                imagettftext($this->image, $fontsize, rand(-30, 30), $x, $y, $fontcolor, $fontFace, $this->checkCode[$i]);
            }
        }
    }

    public function getImageContent(){
        ob_start();
        $this->showImage();
        return ob_get_clean();
    }

    private function outputImage() {
        imagejpeg($this->image);
    }

    function __destruct() {
        imagedestroy($this->image);
    }
}

?>
