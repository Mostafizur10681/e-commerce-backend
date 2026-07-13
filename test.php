<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = \Illuminate\Http\Request::create('/api/change-password', 'POST', ['current_password' => 'wrongpassword', 'password' => 'newpassword123', 'password_confirmation' => 'newpassword123']);
$request->setUserResolver(function() { return \App\Models\User::find(1); });
$controller = app()->make(\App\Http\Controllers\API\AuthController::class);

try {
    $response = $controller->changePassword($request);
    echo json_encode($response->getData());
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation failed:\n";
    echo json_encode($e->errors());
}
