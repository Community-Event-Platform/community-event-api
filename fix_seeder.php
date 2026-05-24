<?php
$content = file_get_contents(__DIR__ . '/database/seeders/EventSeeder.php');

// Add attendees, rating, price after capacity for each event
$pattern = "/('capacity' => \d+,\s*)('event_type' => 'Free')/";
$replacement = "$1'attendees' => 0,\n                'rating' => 4.5,\n                'price' => null,\n                $2";
$content = preg_replace($pattern, $replacement, $content);

$pattern2 = "/('capacity' => \d+,\s*)('event_type' => 'Paid')/";
$replacement2 = "$1'attendees' => 0,\n                'rating' => 4.5,\n                'price' => null,\n                $2";
$content = preg_replace($pattern2, $replacement2, $content);

file_put_contents(__DIR__ . '/database/seeders/EventSeeder.php', $content);
echo "Fixed EventSeeder.php\n";