<?php
/**
 * 功能：支付宝生成二维码
 * 版本：v1.0
 * author：wangkk
 * 以下部分就是具体的生成二维码过程，只需要引入自己的配置文件$config数组信息，同时需要获取订单信息即可使用
 */

//引入sdk文件
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aop/AopClient.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aop/request/AlipayTradePrecreateRequest.php';
//引入配置文件信息
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'config.php';
//引入生成二维码
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'phpqrcode/phpqrcode.php';

class CreateQrcode{
    public function __construct($alipay_config){
        //配置项
        $this->gateway_url       = $alipay_config['gatewayUrl'];
		$this->appid             = $alipay_config['app_id'];
		$this->private_key       = $alipay_config['merchant_private_key'];
		$this->alipay_public_key = $alipay_config['alipay_public_key'];
		$this->charset           = $alipay_config['charset'];
        $this->signtype          = $alipay_config['sign_type'];
        $this->notify_url        = $alipay_config['notify_url'];
        $this->return_url        = $alipay_config['return_url'];

		if(empty($this->appid) || trim($this->appid) == ""){
			throw new Exception("appid不能为空！");
		}
		if(empty($this->private_key) || trim($this->private_key) == ""){
			throw new Exception("商户密钥不能为空！");
		}
		if(empty($this->alipay_public_key) || trim($this->alipay_public_key) == ""){
			throw new Exception("商户公钥不能为空！");
		}
		if(empty($this->charset) || trim($this->charset)== "" ){
			throw new Exception("编码格式不能为空");
		}
		if(empty($this->gateway_url) || trim($this->gateway_url) == ""){
			throw new Exception("支付网关地址不能为空！");
        }
        if(empty($this->notify_url) || trim($this->notify_url) == ""){
            throw new Exception("异步回调地址不能为空！");
        }
    }

    public function pay(){
        //订单号，自定义，唯一
        $out_trade_no = $_GET['out_trade_no'];

        /** --------------------------------以下部分需要修改：获取订单信息 start--------------------------------- **/
        //通过订单号获取到订单信息
        // $orderInfo = M('order')->where(['out_trade_no'=>$out_trade_no])->find();
        // if(empty($orderInfo)){
        //     throw new Exception("查无此订单");
        // }
        // //参数列表
        // $body            = $orderInfo['body'];         //商品描述，可为空
        // $subject         = $orderInfo['subject'];      //订单标题，必填
        // $out_trade_no    = $orderInfo['out_trade_no']; //订单号，必填
        // $total_amount    = $orderInfo['total_amount']; //订单金额，必填
        /** --------------------------------以上部分需要修改：获取订单信息 end--------------------------------- **/
        //订单测试信息
        $body            = '商品描述';         //商品描述，可为空
        $subject         = '订单标题';      //订单标题，必填
        $out_trade_no    = rand(10000,99999); //订单号，必填
        $total_amount    = rand(1,5); //订单金额，必填

        $aopObj = new \AopClient ();
        //设置值
        $aopObj->gatewayUrl = $this->gateway_url;
        $aopObj->appId      = $this->appid;
        $aopObj->rsaPrivateKey = $this->private_key;
        $aopObj->alipayrsaPublicKey = $this->alipay_public_key;
        $aopObj->apiVersion = '1.0';
        $aopObj->postCharset = $this->charset;
        $aopObj->format = 'json';
        $aopObj->signType = $this->signtype;
        
        $request = new AlipayTradePrecreateRequest();
        //组装订单数据
        $timeout_express = '5m';  //超时，1分钟
        $bizContentarr = array(
            'body'            => $body ? $body : '', //商品描述,可以为空
            'subject'         => $subject,
            'out_trade_no'    => $out_trade_no,
            'total_amount'    => $total_amount,
            'timeout_express' => $timeout_express,  //过期时间
        );
        $bizContent = json_encode($bizContentarr,JSON_UNESCAPED_UNICODE);
        $request->setBizContent($bizContent);
        $result = $aopObj->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode) && $resultCode == 10000){
            //成功，得到二维码,不使用官方的方法
            $qr_code_url = $result->$responseNode->qr_code;
            $icon = './img/logo.png';//准备好的logo图片
            \QRcode::png($qr_code_url,false, 'H',  4, false);
            $code           = ob_get_clean();
            $code           = imagecreatefromstring($code);
            $logo           = imagecreatefrompng($icon);
            $QR_width       = imagesx($code);//二维码图片宽度
            $QR_height      = imagesy($code);//二维码图片高度
            $logo_width     = imagesx($logo);//logo图片宽度
            $logo_height    = imagesy($logo);//logo图片高度
            $logo_qr_width  = $QR_width / 4;
            $scale          = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            //重新组合图片并调整大小
            imagecopyresampled($code, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
            header ( "Content-type: image/png" );
            ImagePng($code);
            echo $qrcode;die;
        } else {
            echo 'fail';die;
        }
    }

}
$alipay_config = $config;
$CreateQrcodeObj = new CreateQrcode($alipay_config);
$CreateQrcodeObj->pay();


