<?php
require 'vendor/autoload.php';
require_once( 'lib/woocommerce-api.php' );

$options = array(
	'debug'           => true,
	'return_as_array' => false,
	'validate_url'    => false,
	'timeout'         => 30,
	'ssl_verify'      => false,
);

$client = new WC_API_Client( 'https://fanoffers.com', 'ck_b2b780b5f7a4cd3cd5484cbda1149a26', 'cs_b7b7765ca1b683318809e6b4b5bb804b', $options );

$app = new \Slim\App();
$app->post('/printio-update', function ($request, $response, $args) {
    
    try{
        
        
        } catch ( WC_API_Client_Exception $e ) {

	echo $e->getMessage() . PHP_EOL;
	echo $e->getCode() . PHP_EOL;

	if ( $e instanceof WC_API_Client_HTTP_Exception ) {

		print_r( $e->get_request() );
		print_r( $e->get_response() );
	}
}

   
});
$app->run();
?>




	
