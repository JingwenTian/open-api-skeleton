<?php

namespace middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use helper\Api\ApiException;
use helper\Api\Conf\ApiConf;
use helper\Api\ApiVerifyAuth;
use helper\Api\ApiRequestPathRule;

class AuthMiddleware 
{

    const PROTOCOL_TOKEN  = 'token';     //表示AccessToken 免签名通讯协议
    const PROTOCOL_SECRET = 'signature'; //表示AppId/AppSecret 签名通讯协议

    const ACCESS_TOKEN_FETCH_PATH  = '/api/token'; //获取 access token 的路径

    protected $logger;

    /**
     * 默认配置
     */
    private $options = [
                            "protocol"      => null, //通讯协议,默认为signature
					    	"secure" 		=> true, // 是否开启安全认证(非https域名白名单)
					    	"relaxed" 		=> ["localhost", "127.0.0.1"], //允许请求的安全域
					    	"path" 			=> null, //需要鉴权的路径
					        "passthrough" 	=> null, //无需鉴权的路径
					    ];

	public function __construct( array $options = [] ) 
	{
		// 规则栈
        $this->rules = new \SplStack;

        // 重置默认设置项
        $this->hydrate($options);

        // 配置地址规则
        if (null !== ($this->options["path"])) {
            $this->addRule(new ApiRequestPathRule([
                "path" => $this->options["path"],
                "passthrough" => $this->options["passthrough"]
            ]));
        }
	}

	/**
	 * 鉴权调用入口
	 *
	 * @param RequestInterface  $request  [description]
	 * @param ResponseInterface $response [description]
	 * @param callable          $next     [description]
	 *
	 * @return [type]            [description]
	 */
	public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
    	$scheme = $request->getUri()->getScheme();
        $host   = $request->getUri()->getHost();
        $path   = $request->getUri()->getPath();
        $method = $request->getMethod();

        // 判断路径是否需要鉴权
        if (false === $this->shouldAuthenticate($request)) {
            return $next($request, $response);
        }

        // 判断非 HTTPS 白名单
        if ("https" !== $scheme && true === $this->options["secure"]) {
            if (!in_array($host, $this->options["relaxed"])) {
                throw new ApiException(ApiVerifyAuth::SYS_URL_INSECURE,  ApiConf::SYS_URL_INSERCURE_ERROR_CODE);
            }
        }

        $params = $request->getParams();

        $this->log(LogLevel::DEBUG, '[' . $method . ']' . $path, [$params]);

        // 鉴权
        try{

        	$authHelper = new ApiVerifyAuth();

            if (self::PROTOCOL_TOKEN == $this->options["protocol"]) {
                $authHelper->_checkAccessToken($params);
            } else {
                $authHelper->_checkSignature($params);
            }

        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getCode());
        }

        // 鉴权成功
        return $next($request, $response);
        
    }


    /**
     * 检测是否需要鉴权
     *
     * @param RequestInterface $request [description]
     *
     * @return [type]           [description]
     */
    public function shouldAuthenticate(RequestInterface $request)
    {
        foreach ($this->rules as $callable) {
            if (false === $callable($request)) {
                return false;
            }
        }
        return true;
    }


    /**
     * 设置安全校验标识
     *
     * @param [type] $secure [description]
     */
    public function setSecure($secure)
    {
        $this->options["secure"] = !!$secure;
        return $this;
    }

    /**
     * 获取安全校验标识
     *
     * @return [type] [description]
     */
    public function getSecure()
    {
    	return $this->options["secure"];
    }

    /**
     * 设置域名白名单
     *
     * @return [type] [description]
     */
    public function setRelaxed(array $relaxed)
    {
        $this->options["relaxed"] = $relaxed;
        return $this;
    }

    /**
     * 获取域名白名单
     *
     * @return [type] [description]
     */
    public function getRelaxed()
    {
        return $this->options["relaxed"];
    }

    /**
     * 获取鉴权地址
     *
     * @return string
     */
    public function getPath()
    {
        return $this->options["path"];
    }

    /**
     * 设置鉴权地址
     *
     * @param string|string[] $$path
     * @return self
     */
    public function setPath($path)
    {
        $this->options["path"] = $path;
        return $this;
    }

    /**
     * 获取通讯协议
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->options["protocol"];
    }

    /**
     * 设置通讯协议
     *
     * @param [type] $method [description]
     */
    public function setProtocol($protocol)
    {
        $this->options["protocol"] = $protocol;
        return $this;
    }

    /**
     * 获取鉴权忽略地址
     *
     * @return string|array
     */
    public function getPassthrough()
    {
        return $this->options["passthrough"];
    }

    /**
     * 设置鉴权忽略地址
     *
     * @param string|string[] $passthrough
     * @return self
     */
    public function setPassthrough($passthrough)
    {
        $this->options["passthrough"] = $passthrough;
        return $this;
    }


    /**
     * 规则入栈
     *
     * @param callable $callable Callable which returns a boolean.
     * @return self
     */
    public function addRule($callable)
    {
        $this->rules->push($callable);
        return $this;
    }

    /**
     * 批量设置规则
     *
     * @param array $rules [description]
     */
    public function setRules(array $rules)
    {
        /* Clear the stack */
        unset($this->rules);
        $this->rules = new \SplStack;
        /* Add the rules */
        foreach ($rules as $callable) {
            $this->addRule($callable);
        }
        return $this;
    }

    /**
     * 获取规则栈
     *
     * @return [type] [description]
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * 获取日志
     *
     * @return \Psr\Log\LoggerInterface $logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * 设置日志
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * 记录日志
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            return $this->logger->log($level, $message, $context);
        }
    }

    /**
     * 批量设置配置项
     *
     * @param array $data [description]
     *
     * @return [type] [description]
     */
    private function hydrate(array $data = [])
    {
        if (
            isset($data['protocol']) && 
            self::PROTOCOL_TOKEN == $data['protocol']
        ) {
            if (isset($data['passthrough'])) {
                is_array($data['passthrough']) && array_push($data['passthrough'], self::ACCESS_TOKEN_FETCH_PATH);
                is_string($data['passthrough']) && $data['passthrough'] = [$data['passthrough'], self::ACCESS_TOKEN_FETCH_PATH];
            } else {
                $data['passthrough'] = self::ACCESS_TOKEN_FETCH_PATH;
            }
        }
        foreach ($data as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func(array($this, $method), $value);
            }
        }
        return $this;
    }



}