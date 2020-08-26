# Shipy
 Php Class for Shipy Virtual Pos
 
# Creating a Sample Payment Page

```
<?php

require 'Shipy.php'; 

$shipy = new Shipy();

$shipy->setConfig([
    'type' => 'init', # Config Type
    'api_key' => '****************', # Shipy Merchant Key
    'payment_method' => 'cc', # Payment Method | cc - mobile
]);

/*$shipy->setLocale([
    'currency' => 'TRY', # Locale Currency
    'page' => 'TR', # Locale Page Lang
    'mail' => 'TR', # Locale Mail Lang
]);*/

$shipy->setCustomer([
    'name' => 'customer.name', # Customer Name
    'email' => 'customer.mail@gmail.com', # Customer Mail
    'phone' => 'customer.phone', # Customer Phone Number
    'address' => 'customer.address' # Customer Address
]);

$shipy->setProduct([
    'order_id' => 508, # Product Order ID
    'amount' => 0.1, # Product Amount
    'installment' => 0 # Product Installment
]);

echo '<iframe src="' . $shipy->init() . '" frameborder="0" scrolling="no" width="100%" height="100%"></iframe>';
```

# Creating a Sample CallBack Page

```
<?php

require 'Shipy.php';

$shipy = new Shipy();

$shipy->setConfig([
    'type' => 'callback', # Config Type
    'api_key' => '****************', # Shipy Merchant Key
]);

$result = $shipy->callback();

if ($result['return_id'] != null) {
//  echo "<pre>";
//  print_r($result);
//  echo "</pre>";
    # success Action
    echo "OK";
    exit();
}
```