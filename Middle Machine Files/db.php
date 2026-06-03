<?php

$serverName = "192.168.1.16";

$connectionOptions = array(
    "Database" => "Mypos_DB",
    "Uid" => "sa",
    "PWD" => "asb@123",

    // 🔥 FIX FOR ODBC DRIVER 18 SSL ERROR
    "TrustServerCertificate" => true,
    "Encrypt" => false
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

echo "Connection Success!";
?>