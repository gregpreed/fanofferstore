<?php
require 'vendor/autoload.php';
require_once( 'lib/woocommerce-api.php' );

$app = new \Slim\Slim();
$app->post('/printio-update', function () use ($app){
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    
$options = array(
	'debug'           => true,
	'return_as_array' => false,
	'validate_url'    => false,
	'timeout'         => 30,
	'ssl_verify'      => false,
);

try {

	$client = new WC_API_Client( 'https://fanoffers.com', 'ck_b2b780b5f7a4cd3cd5484cbda1149a26', 'cs_b7b7765ca1b683318809e6b4b5bb804b', $options );
    
     
    for ($i =0; $i <= count($data['Items']); $i++) {
    if( isset( $data['Items'][$i]['TrackingNumber']) ){
        print_r( $client->order_notes->create( 1201, array( 'note' => 'Tracking Number: '.$data['Items'][$i]['TrackingNumber'].' Tracking URL: '.$data['Items'][$i]['TrackingUrl'] ) ) );
        }
    
        
}
    
}
    
    
}); 
$app->run();
?>




	
