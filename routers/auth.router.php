<?php 

/**
 * 获取 Access Token
 */

use helper\Api\ApiVerifyAuth;
use helper\Api\ApiException;
use helper\Api\Conf\ApiConf;

$app->post('/api/token', function($request, $response, $args = []) use ($app) {

	$appId = $request->getParam('app_id', '');
	$appSecret = $request->getParam('app_secret', '');

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
					'expires_in' 	=> ApiConf::OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME, //凭证有效时间，单位：秒
				];

	$app->responseHandeler(ApiConf::SUCCESS_CODE, 'access token fetch success', $response);
	
});