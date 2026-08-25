<?php
use Base\Foundation\Security\Public\ValueObjects\SensitiveValue;

require __DIR__.'/vendor/autoload.php';

$secret = new SensitiveValue('my-super-secret');
$payload = serialize($secret);
echo $payload . "\n";
