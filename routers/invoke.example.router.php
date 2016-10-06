<?php 

/**
 * 调用示例
 */

$app->get('/example', function() {

	$appID 		= '888888';
    $appSecret 	= 'qazwsxcderfvbgtyhnmju';

    // ==================================================================
    // AccessToken 免签名通讯协议 示例
    // ------------------------------------------------------------------
    
    // STEP 1: 根据 APPID,APPSECRET 获取 ACCESS_TOKEN; GET 或 POST 请求

	$params = [
		        'app_id'    => $appID,
		        'app_secret'=> $appSecret,
		    ];
	$url = 'http://php.dev/api/token';
	$response = curl($url, $params, [], 'GET');
	$accessToken = isset($response['data']['access_token']) ? $response['data']['access_token'] : ''; //3600秒有效期

	// STEP 2: 携带 ACCESS_TOKEN 请求测试接口

	$url = 'http://php.dev/api/v1/products';
	$params = [
				'access_token' => $accessToken,
		    ];
	$result = curl($url, $params, [], 'GET');

	p($result);die;


    // ==================================================================
    // AppId/AppSecret 签名通讯协议
    // ------------------------------------------------------------------

    $params = [
    			// 系统参数
		        'app_id'    => $appID,
		        'timestamp' => date('Y-m-d H:i:s'),
		        'nonce'		=> rand(),
		        // 业务参数
		        'product_id'=> 2
		    ];

    // STEP 1 根据请求参数生成签名
    $createSign = function($data) use ($appSecret) {

    	ksort($data);
	    $string = '';

	    $isFirst = true;
	    foreach ($data as $key => $itemVal) {
	    	if (!$isFirst) { 
	            $string = $string . "&";
	        }
	        $isFirst= false;

	        //拼接签名原文时，如果参数名称中携带 '_', 需要替换成 '.' 
	        if(strpos($key, '_')) {
	            $key = str_replace('_', '.', $key);
	        }
	        $string .= urlencode($key) . '=' . urlencode($itemVal);
	    }
	    //根据秘钥生成加密串
	    return urlencode(base64_encode(hash_hmac('sha1', $string, $appSecret, true)));

    };
    $params['signature'] = call_user_func($createSign, $params);

    // STEP 2: 携带签名请求测试接口
    $url = 'http://php.dev/api/v1/product/2';
    $result = curl($url, $params, [], 'GET');

    p($result);die;
});
