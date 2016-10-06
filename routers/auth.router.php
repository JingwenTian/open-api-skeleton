<?php 

/**
 * 获取access_token
 * access_token是全局唯一接口调用凭据
 */

use helper\Api\ApiVerifyAuth;
use helper\Api\ApiException;
use helper\Api\Conf\ApiConf;

$app->map( ['GET', 'POST'], '/api/token', function($request, $response, $args = []) use ($app) {

	$appId = $request->getParam('app_id', ''); // 第三方用户唯一凭证
	$appSecret = $request->getParam('app_secret', ''); // 第三方用户唯一凭证密钥

	$authHelper = new ApiVerifyAuth();

	if (!$appId || !$appSecret) {
		throw new ApiException( "invalid parameter" , ApiConf::INVALID_PARAMETER ); 
	}

	if (!$authHelper->_verifyClient($appId, $appSecret)) {
		throw new ApiException("invalid app_id", ApiConf::INVALID_APP_ID);
	}

	$accessToken = $authHelper->_fetchToken( $appId );

	$response = [
					'access_token' 	=> $accessToken, //全局唯一接口调用凭证
					'expires_in' 	=> ApiConf::OAUTH2_ACCESS_TOKEN_EXPIRES, //凭证有效时间，单位：秒
				];

	$app->responseHandeler(ApiConf::SUCCESS_CODE, 'access token fetch success', $response);
	
});