<?php
define('SECRET_KEY', 'smart_appointment_secret_key_change_this_2026');

function createToken($id) {
    $id = (string)$id;
    $signature = hash_hmac('sha256', $id, SECRET_KEY);
    return base64_encode($id . '|' . $signature);
}

function verifyToken($token) {
    $decoded = base64_decode($token);

    if (!$decoded || strpos($decoded, '|') === false) {
        return false;
    }

    list($id, $signature) = explode('|', $decoded, 2);
    $validSignature = hash_hmac('sha256', $id, SECRET_KEY);

    if (!hash_equals($validSignature, $signature)) {
        return false;
    }

    return $id;
}
?>  