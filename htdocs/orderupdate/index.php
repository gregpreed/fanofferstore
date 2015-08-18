<?php
require 'vendor/autoload.php';
require_once( 'lib/woocommerce-api.php' );

$app = new \Slim\Slim();
$app->get('/hello/:name', function ($name) {
    echo "Hello, " . $name;
});
$app->run();
?>