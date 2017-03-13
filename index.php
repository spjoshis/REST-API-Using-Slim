<?php


	ini_set('display_errors', true);
	error_reporting(E_ALL ^ E_NOTICE);


	require 'vendor/autoload.php';
	use \Psr\Http\Message\RequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;
	require 'pdoWrapper.php';

	$dbConfig = array(
		'host' => 'localhost',
		'dbName' => 'db_slim',
		'dbUser' => 'root',
		'dbPassword' => '123456'
	);

	$app = new \Slim\App( array("MODE" => "developement") );
	$db = new Database($dbConfig);

	$app->post('/login', function (Request $request, Response $response, $args) use ($app, $db) {
		$apiResponse = array();
		$username = $request->getParam('username');
	    $password = $request->getParam('password');
		if (!empty($username) && !empty($password)) {
			$code        = 200;
			$apiResponse = $db->login($username, $password);
	    }else{
			$code                   = 401;
			$apiResponse['status']  = 'fail';
			$apiResponse['message'] = 'Please enter required details';
	    }
	    return $response->withJson($apiResponse, $code);
	});

	$app->post('/logout', function (Request $request, Response $response, $args) use ($app, $db) {
		$apiResponse = array();
		$token = $request->getParam('token');
		if (!empty($token)) {
			$code        = 200;
			$apiResponse = $db->logout($token);
	    }else{
			$code                   = 401;
			$apiResponse['status']  = 'fail';
			$apiResponse['message'] = 'Invalid Token';
	    }
	    return $response->withJson($apiResponse, $code);
	});

	$app->post('/leaves', function (Request $request, Response $response, $args) use ($app, $db) {
		$apiResponse = array();
		$token = $request->getParam('token');
		if (!empty($token)) {
			$code        = 200;
			$apiResponse = $db->getLeaves($token);
	    }else{
			$code                   = 401;
			$apiResponse['status']  = 'fail';
			$apiResponse['message'] = 'Invalid Token';
	    }
	    return $response->withJson($apiResponse, $code);
	});

	$app->run();