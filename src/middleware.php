<?php 
// Application middleware

$app->add(new \middleware\AuthMiddleware([
	"protocol"		=> "signature", 		//通讯协议 signature 或 token
	"secure" 		=> true,
	"relaxed"		=> ["php.dev"],
	"path" 			=> ["/api"], 			//需要鉴权的路径
	"passthrough"	=> ["/api/doc"], 		//无需鉴权的路径
	"logger"		=> $container['logger']
]));