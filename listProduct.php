<?php

$array = [];

$product = '{"styleNumber": "ABC|123", "name": "T-Shirt", "price": {"amount": 1500, "currency": "USD"}, "images": ["https://via.placeholder.com/400x300/4b0082?id=1", "https://via.placeholder.com/400x300/4b0082?id=2"]}';

for($i=0; $i<50000; $i++){
    $array[] = $product;
}

$json = '[';
$json .= implode(',',$array);
$json .= ']';

file_put_contents('products.json', $json);
