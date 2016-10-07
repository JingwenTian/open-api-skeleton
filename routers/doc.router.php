<?php 

/**
 * Open Api 说明文档
 */

$app->get('/api/doc', function($request, $response, $args) {
	
	return $this->renderer->render($response, 'doc/index.phtml', $args);

});