<?php 

namespace helper\Api;
use \Psr\Http\Message\RequestInterface;

class ApiRequestPathRule
{

    protected $options = [
        "path" => ["/"],
        "passthrough" => []
    ];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function __invoke(RequestInterface $request)
    {
        $uri = "/" . $request->getUri()->getPath();
        $uri = str_replace("//", "/", $uri);

        /* If request path is matches passthrough should not authenticate. */
        foreach ((array)$this->options["passthrough"] as $passthrough) {
            $passthrough = rtrim($passthrough, "/");
            if (!!preg_match("@^{$passthrough}(/.*)?$@", $uri)) {
                return false;
            }
        }

        /* Otherwise check if path matches and we should authenticate. */
        foreach ((array)$this->options["path"] as $path) {
            $path = rtrim($path, "/");
            if (!!preg_match("@^{$path}(/.*)?$@", $uri)) {
                return true;
            }
        }
        return false;
    }
}
