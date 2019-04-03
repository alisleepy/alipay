<?php
/**
 * 功能：支付宝支付测试文件
 * 版本：v1.0
 * author：wangkk
 * 以下部分就是具体的支付过程，只需要引入自己的配置文件$config数组信息，同时需要获取订单信息即可使用
 */

//引入sdk文件
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aop/AopClient.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'aop/request/AlipayTradeWapPayRequest.php';
//引入配置文件信息
require dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'config.php';

/**
 * 支付宝支付类的封装
 */
class Alipay{
    //配置文件数据
    public $alipay_config;
    //构造函数，获取数据
    public function __construct($alipay_config){
        //支付网关
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
        $orderInfo = M('order')->where(['out_trade_no'=>$out_trade_no])->find();
        if(empty($orderInfo)){
            throw new Exception("查无此订单");
        }
        //参数列表
        $body            = $orderInfo['body'];         //商品描述，可为空
        $subject         = $orderInfo['subject'];      //订单标题，必填
        $out_trade_no    = $orderInfo['out_trade_no']; //订单号，必填
        $total_amount    = $orderInfo['total_amount']; //订单金额，必填
        /** --------------------------------以上部分需要修改：获取订单信息 end--------------------------------- **/
        //订单测试信息
        // $body            = '商品描述';         //商品描述，可为空
        // $subject         = '订单标题';      //订单标题，必填
        // $out_trade_no    = rand(10000,99999); //订单号，必填
        // $total_amount    = rand(1,5); //订单金额，必填

        $timeout_express = '1m';  //超时，1分钟
        $product_code    = 'QUICK_WAP_WAY';  //手机端支付宝
        if(empty($subject) || trim($subject) == ""){
            throw new Exception("订单标题不能为空");
        }
        if(empty($total_amount) || trim($total_amount) == ""){
            throw new Exception("订单金额不能为空");
        }
        
        //组装订单数据
        $bizContentarr = array(
            'body'            => $body ? $body : '', //商品描述,可以为空
            'subject'         => $subject,
            'out_trade_no'    => $out_trade_no,
            'total_amount'    => $total_amount,
            'timeout_express' => $timeout_express,
            'product_code'    => $product_code,
        );
        $bizContent = json_encode($bizContentarr,JSON_UNESCAPED_UNICODE);
        
        //设置数据
        $aopObj = new \AopClient();
        $aopObj->gatewayUrl = $this->gateway_url;
        $aopObj->appId = $this->appid;
        $aopObj->rsaPrivateKey = $this->private_key;
        $aopObj->alipayrsaPublicKey = $this->alipay_public_key;
        $aopObj->apiVersion = '1.0';
        $aopObj->postCharset = $this->charset;
        $aopObj->format = 'json';
        $aopObj->signType = $this->signtype;

        //设置请求的数据
        $request = new \AlipayTradeWapPayRequest ();
        $request->setBizContent($bizContent);
        $request->setNotifyUrl($this->notify_url);
        $request->setReturnUrl($this->return_url);
        $result = $aopObj->pageExecute($request);
        echo $result;
    }
}

//获取到配置文件，框架里的话直接放在配置文件中，通过框架方法去获取
$configInfo = $config;
$AlipayObj = new Alipay($configInfo);
$AlipayObj->pay();