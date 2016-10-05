<?php 

use helper\Api\ApiException;
use helper\Api\Conf\ApiConf;

$app->group('/api/v1', function() use ($app) {

	$this->get('/products', function ($request, $response, $args) use ($app) {
		$mock = [
					[
						'id' 	=> 1,
						'name'	=> 'product1'
					],
					[
						'id' 	=> 2,
						'name'	=> 'product2'
					]
				];
		$app->responseHandeler(ApiConf::SUCCESS_CODE, 'success', $mock);
	});

});
