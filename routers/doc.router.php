<?php 

/**
 * Open Api 说明文档
 */

$app->get('/doc', function ($req, $res, $args) {
	return $res->withStatus(302)->withHeader('Location', '/v1/doc');
});

$app->group('/v1', function() {

	$this->get('/doc', function($request, $response, $args) {
		echo 'api document';
	});

});