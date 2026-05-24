<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

DB::table('events')->whereIn('id', [4,5,6,7,8,9])->delete();
echo "Deleted old events\n";