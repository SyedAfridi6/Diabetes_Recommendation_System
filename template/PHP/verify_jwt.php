<?php
function verifyTokenWithFlask($token) {
    $flask_url = "http://localhost:5000/verify_token";

    $ch = curl_init($flask_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["token" => $token]));

    $response = curl_exec($ch);

    if ($response === false) {
        return ["valid" => false, "error" => "Failed to contact Flask verification server."];
    }

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_status !== 200) {
        return ["valid" => false, "error" => $result["error"] ?? "Unknown error"];
    }

    return ["valid" => true, "user_data" => $result["user_data"]];
}
?>
