<?php 

/**
 * 调用示例
 */

$app->get('/example', function() {

	$appID 		= '888888';
    $appSecret 	= 'qazwsxcderfvbgtyhnmju';

    // 生成签名
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

	    return urlencode(base64_encode(hash_hmac('sha1', $string, $appSecret, true)));

    };

    // POST 请求
    $requestUrl = function($data) {
    	$url = 'http://php.dev/api/token';
	    $response = curl($url, [], $data, 'POST');

	    return $response;
    };

    // 获取 Access Token
	$accessToken = function() use ($appID, $appSecret, &$createSign, &$requestUrl) {
		// 组装请求参数
		$data = [
	        //系统基本参数
	        'app_id'    => $appID,
	        'app_secret'=> $appSecret,
	        'timestamp' => date('Y-m-d H:i:s'),
	    ];

	    $data['signature'] = call_user_func($createSign, $data);
	    $token = call_user_func($requestUrl, $data);

	    return isset($token['data']['access_token']) ? $token['data']['access_token'] : '';
	};

	// 全局唯一接口调用凭证, 有效期3600秒
	$token    = $accessToken();

	if (empty($token)) {
		die('请求token失败');
	}

	// 通过 Access Token 获取测试接口数据

	$apiUrl = 'http://php.dev/api/v1/products';
	$data = [
				'access_token' => $token
		    ];
    $data['signature'] = call_user_func($createSign, $data);
    $result = curl($apiUrl, $data, [], 'GET');

    p($result);
});
