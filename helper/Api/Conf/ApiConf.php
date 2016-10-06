<?php 

namespace helper\Api\Conf;

class ApiConf
{
	const OAUTH2_ACCESS_TOKEN_EXPIRES 	= 3600; //Access Token生存时间
	const OAUTH2_ACCESS_TOKEN_SALT 		= "eba7aa43d165fc6bf49c0549a8a55d35"; //token加密
	const OAUTH2_ACCESS_TOKEN_LENGTH 	= 64; //token长度


	const SUCCESS_CODE           			= 10000; //成功返回码

    const SYS_PARAM_ERROR_CODE         		= 11000; //url 缺失系统公共request参数
    const SYS_URL_TIME_ERROR_CODE      		= 11001; //url 连接失效， 即时间参数失效 超过了系统时间的设置
    const SYS_URL_INSERCURE_ERROR_CODE      = 11002; //url 非安全链接
    const SYS_SIGN_ERROR_CODE          		= 11003; //url 签名参数错误
    const SYS_API_NOT_FOUND_ERROR_CODE 		= 11004; //api不存在
    const SYS_API_UNAUTHORIZED_ERROR_CODE	= 11005; // api 未授权

	const INVALID_ACCESS_TOKEN   			= 10001; // token错误
	const INVALID_PARAMETER      			= 10002; // 错误的传参
	const INVALID_APP_ID         			= 10003; // 不合法的AppID
	const INVALID_APP_SECRET     			= 10004; // 不合法的AppSecret
	
}
