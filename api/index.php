<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

return $app->handle(
    Illuminate\Http\Request::capture()
);