<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$orders = \App\Models\Order::all();
foreach ($orders as $o) {
    echo "ID: {$o->id}, Number: {$o->order_number}, Status: {$o->status}, Total: {$o->total}\n";
}

