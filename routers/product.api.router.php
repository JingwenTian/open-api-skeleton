<?php 

use helper\Api\ApiException;
use helper\Api\Conf\ApiConf;

$app->group('/api/v1', function() use ($app) {

	$mock = [
				1 => [
						'id' 	=> 1,
						'name'	=> 'product1',
						'price' => 100
					],
				2 => [
						'id' 	=> 2,
						'name'	=> 'product2',
						'price' => 200
					]
			];

	$this->get('/products', function ($request, $response, $args) use ($app, $mock) {
		
		$app->responseHandeler(ApiConf::SUCCESS_CODE, 'success', $mock);

	});

	$this->get('/product/{product_id}', function ($request, $response, $args) use ($app, $mock) {

		$requestId = $request->getParam('product_id'); //校验参数
		$id = isset($args['product_id']) ? intval($args['product_id']) : 0;

		if ($requestId != $id) {
			return $app->responseHandeler(ApiConf::SUCCESS_CODE, 'error', []);
		}

		if (isset($mock[$id])) {
			return $app->responseHandeler(ApiConf::SUCCESS_CODE, 'success', $mock[$id]);
		}

		return $app->responseHandeler(ApiConf::SUCCESS_CODE, 'empty', []);
	});

});
