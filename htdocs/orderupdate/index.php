<?php
require 'vendor/autoload.php';
require_once( 'lib/woocommerce-api.php' );

$app = new \Slim\Slim();
$app->post('/printio-update', function () use ($app){
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    
    for ($i =0; $i <= count($data[Items]); $i++) {
    
        if ($data[Items][$i][TrackingNumber]){
    print_r ('Tracking Number: '.$data[Items][$i][TrackingNumber].' Tracking URL: '.$data[Items][$i][TrackingUrl]);
        }
    
}
    
    
}); 
$app->run();
?>




	
