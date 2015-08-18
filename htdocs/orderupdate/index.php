<?php
require 'vendor/autoload.php';
require_once( 'lib/woocommerce-api.php' );

$app = new \Slim\Slim();
$app->post('/printio-update', function () {
    
    
    $paramValue = $app->request->post('Hello');
    echo $paramValue;
  
});
$app->run();
?>




	
