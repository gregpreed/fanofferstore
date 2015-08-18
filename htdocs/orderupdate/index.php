<?php
require 'vendor/autoload.php';
require_once( 'lib/woocommerce-api.php' );

$app = new \Slim\App();
$app->post('/printio-update', function ($request, $response, $args) {
    
    
    $paramValue = $app->request->post('Hello');
    echo $paramValue;
  
});
$app->run();
?>




	
