<?php
$ch = curl_init('https://script.google.com/macros/s/AKfycbwoqlBOLBHuq4iHDoD5Pq6yMKL4rddAgRrYEjmkWPjya-aIn4l_T6DSznSdIeTtznT1/exec');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['tableName' => 'DATA Gudang', 'action' => 'Find']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
echo "Response:\n$response\n";
