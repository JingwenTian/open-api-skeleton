<?php 
// Application middleware

$app->add(new \middleware\AuthMiddleware([
	"secure" 		=> true,
	"relaxed"		=> ["php.dev"],
	"path" 			=> ["/api"], 			//需要鉴权的路径
	"passthrough"	=> ["/api/doc"], 		//无需鉴权的路径
	"access"		=> "/api/token", 		//获取 Access Token 的路径
	"logger"		=> $container['logger']
]));