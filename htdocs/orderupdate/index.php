<?php
require "Slim/Slim.php";
$app = new Slim();

$app->get('/', function() {     
    get_header();
    echo "hello";
    get_footer();
});

$app->run(); 
?>