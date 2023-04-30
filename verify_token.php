<?php
try {
    if (!isset(apache_request_headers()["Authorization"]))
        throw new Exception("Invalid request");

    $authHeader = apache_request_headers()["Authorization"];
    $token = null;

    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
    }
    if (!$token)
        throw new Exception("Token not found");

    require 'JwtHandler.php';
    $jwt = new JwtHandler();

    $tokenDecoded = $jwt->jwtDecodeData($token);

    $tokenExpireAt = date('Y-m-d H:i:s', $tokenDecoded->exp);
    if ($tokenExpireAt < date('Y-m-d H:i:s'))
        throw new Exception("Token is expired");

    include('db_connection.php');
    $qry = mysqli_query($conn, 'SELECT * FROM `users` WHERE `id` = ' . $tokenDecoded->data->id);
    if (mysqli_num_rows($qry) <= 0)
        throw new Exception("Token mismatch");
    $authData = mysqli_fetch_assoc($qry);
} catch (Exception $e) {
    echo json_encode(['success' => 0, 'Error' => $e->getMessage()]);
    exit;
}
