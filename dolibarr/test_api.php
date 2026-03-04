<?php

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://dolibarr.sm.syncstats.ca/api/index.php/about");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "DOLAPIKEY: 62AqPM4Tf8xAFlxmTx4Fk368YM4T0edu"
]);

$response = curl_exec($ch);
curl_close($ch);

echo "<pre>";
print_r($response);