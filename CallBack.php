<?php

require 'Shipy.php';

$shipy = new Shipy();

$shipy->setConfig([
	'type' => 'callback', # Config Type
    'api_key' => '****************', # Shipy Merchant Key
]);

$result = $shipy->callback();

if ($result['return_id'] != null) {
//	echo "<pre>";
//	print_r($result);
//	echo "</pre>";
	# success Action
	echo "OK";
	exit();
}