<?php
$db = \Config\Database::connect();
$db_prefix = $db->getPrefix();

$methods = $db->table($db_prefix . 'payment_methods')
    ->where('deleted', 0)
    ->get()
    ->getResult();

echo "<pre>";
echo "Available Payment Methods:\n";
print_r($methods);
echo "</pre>";