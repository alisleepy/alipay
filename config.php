<?php
$config = array (
		//应用ID,您的APPID。
		'app_id' => "***",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "***",
		
		//异步通知地址,支付成功后回调地址，比如修改订单状态等
		'notify_url' => "http://www.alipaydemo/notify_url.php",
		
		//同步跳转
		//'return_url' => "http://mitsein.com/alipay.trade.wap.pay-PHP-UTF-8/return_url.php",
		'return_url' => "",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//生产环境支付宝网关
		//'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
		//沙箱环境支付网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "***",
		
	
);