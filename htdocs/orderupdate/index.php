<?php
require 'vendor/autoload.php';
require_once( 'lib/woocommerce-api.php' );

$app = new Slim();
$app->post('/printio-update', 'addNote'); 
$app->run();


function addNote() {
    $request = Slim::getInstance()->request();
    $note = json_decode($request->getBody());
    echo json_encode($note);
}

?>




	
