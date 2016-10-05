<?php 

namespace helper\Api;
use helper\Api\Conf\ApiConf;
use Hashids\Hashids;

class ApiVerifyAuth
{
	protected $_requestData      = [];
    protected $_appId;
    protected $_appSecret;

    const TIMESTAMP_INTERVAL_NUM 		= 800; // 调用有效期800秒

    const SYS_PARAM_ERROR         		= 'missing the public system parameters';
    const SYS_URL_TIME_ERROR      		= 'time parameter error in url';
    const SYS_URL_INSECURE        		= 'Insecure use of system over HTTP denied by configuration';
    const SYS_SIGN_ERROR          		= 'signature verification failed';
    const SYS_API_NOT_FOUND_ERROR 		= 'api does not exist';
    const SYS_API_UNAUTHORIZED_ERROR 	= 'you are not authorized for current api';

    // 用于测试的模拟 APPID 和 APPSECRET
    const APPID 						= '888888';
    const APPSECRET 					= 'qazwsxcderfvbgtyhnmju';
    

    /**
     * 获取 Token
     */
    public function _fetchToken( $appId )
    {
    	$hashids = new Hashids(
					ApiConf::OAUTH2_ACCESS_TOKEN_SALT ,
					ApiConf::OAUTH2_ACCESS_TOKEN_LENGTH 
				);

		$data = [
					$appId, 
					date('U') + ApiConf::OAUTH2_DEFAULT_ACCESS_TOKEN_LIFETIME 
				];
		
		return  $hashids->encode($data);
    }


    /**
     * 解析 Token
     */
    public function _decodeToken( $token )
    {
    	if( !$token ) {
			return false;
		}

		$hashids = new Hashids(
					ApiConf::OAUTH2_ACCESS_TOKEN_SALT ,
					ApiConf::OAUTH2_ACCESS_TOKEN_LENGTH 
				);
		return  $hashids->decode($token);
    }

    
    /**
     * 鉴权入口方法
     *
     * @param array  $params 接口参数
     * @param string $method 接口名称
     *
     * @return [type] [description]
     */
    public function _verifyAuth( $params = [], $method = '' )
    {
    	$this->_setRequestData($params);
    	return $this->_handleRequestApiAuthIsLegally($method);
    }

    /**
     * 处理request数据格式
     *
     * @param [type] $data [description]
     */
    protected function _setRequestData( $data = [] )
    {
        if (!empty($data)) {
            $this->_requestData = $data;
        } else if (!empty($_GET)) {
            $this->_requestData = $_GET;
        } else if (!empty($_POST)) {
            $this->_requestData = $_POST;
        }

        if (empty($this->_requestData)) {
            throw new ApiException(self::SYS_PARAM_ERROR, ApiConf::SYS_PARAM_ERROR_CODE);
        }

        foreach ($this->_requestData as $key => $item) {
            $key  = urldecode($key);
            $item = urldecode($item);
            $this->_requestData[$key] = $item;
        }
    }

    /**
     * 权限验证主方法
     *
     * @return [type] [description]
     */
    protected function _handleRequestApiAuthIsLegally($method)
    {
        // 检查系统级别参数
        $this->_checkSysRequestParam();

        // 检查app_id 是否存在或被禁用 && 获取secret
        $this->_appId  	  = $this->_requestData['app_id'];
        $this->_appSecret = $this->_getSecretKeyByAppId();

        //检查签名认证
        $signature          = $this->_requestData['signature'];
        $this->_checkSign($signature);

        //TODO 检查APPID是否有权限调用当前api

        //TODO 调用次数验证

        $apiConf['app_id'] 		= $this->_appId;
        $apiConf['app_secret'] 	= $this->_appSecret;
        return $apiConf;
    }

    /**
     * 检查系统级别参数
     *
     * @return [type] [description]
     */
    protected function _checkSysRequestParam()
    {
        $appId      = array_get($this->_requestData, 'app_id', false);
        $timestamp  = array_get($this->_requestData, 'timestamp', false);
        $signature  = array_get($this->_requestData, 'signature', false);

        //检查系统参数
        if (
        	empty($appId) || empty($timestamp) || empty($signature)
        ) {
            throw new ApiException(self::SYS_PARAM_ERROR, ApiConf::SYS_PARAM_ERROR_CODE);
        }

        //检查时间参数
        $currentTime = time();
        $startTime   = $currentTime - self::TIMESTAMP_INTERVAL_NUM;
        $endTime     = $currentTime + self::TIMESTAMP_INTERVAL_NUM;
        $requestTime = strtotime($timestamp);
        
        if ($requestTime < $startTime || $requestTime > $endTime) {
            throw new ApiException(self::SYS_URL_TIME_ERROR, ApiConf::SYS_URL_TIME_ERROR_CODE);
        }
    }

    /**
     * 检查数字签名
     *
     * @param string $signMethod 签名方法
     * @param string $sign       数字签名
     *
     * @return void
     */
    protected function _checkSign($signature)
    {
        if(
        	$signature != urldecode($this->_buildSignChar())
        ) {
            throw new ApiException(self::SYS_SIGN_ERROR, ApiConf::SYS_SIGN_ERROR_CODE);
        }
    }

    /**
     * 生成签名原文
     * 
     * @param [type] $signMethod [description]
     *
     * @return [type] [description]
     */
    protected function _buildSignChar()
    {
        $ReqParaArray = $this->_requestData;

        unset($ReqParaArray['signature']);

        ksort($ReqParaArray); //对请求参数 按参数名 做字典序升序排列, 注意此排序区分大小写

        $string = '';

        $isFirst = true;
        foreach ($ReqParaArray as $key => $itemVal) {
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

        $signature = base64_encode(hash_hmac('sha1', $string, $this->_appSecret, true));

        return urlencode($signature);
    }

    /**
     * 验证 Access Token
     *
     * @return [type] [description]
     */
    public function _checkAccessToken( $params )
    {
    	$access_token = array_get($params, 'access_token', false);
		$data = $this->_decodeToken( $access_token );

		if( $data === false  ){
			throw new ApiException( "invalid access token" , ApiConf::INVALID_ACCESS_TOKEN ); 
		}

		list( $appId , $expires ) = $data;

		if( date('U') > $expires ){

			throw new ApiException( "invalid access token" , ApiConf::INVALID_ACCESS_TOKEN ); 
		}

		$this->_appId =  $appId;

    }



    /**
     * 验证 APPID 和 APPSECRET
     *
     * @param [type] $appId     [description]
     * @param [type] $appSecret [description]
     *
     * @return [type] [description]
     */
    public function _verifyClient( $appId, $appSecret )
    {
    	//  TODO 数据暂时写死, 实际应用时需存储
    	if ($appId == self::APPID && $appSecret == self::APPSECRET) {
    		return true;
    	}
    	return false;
    }

    /**
     * 根据 APPID 获取 APPSECRET
     *
     * @return [type] [description]
     */
    public function _getSecretKeyByAppId( $appId = '' )
    {
    	empty($appId) && $appId = $this->_appId;

    	//  TODO 数据暂时写死, 实际应用时需从数据库获取
    	return self::APPSECRET;
    }

}