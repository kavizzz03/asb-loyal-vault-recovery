<?php

header('Content-Type: application/json');

include 'db.php';

$apiKey = "ASB2026SECRET";

$key = $_GET['key'] ?? '';
$search = trim($_GET['search'] ?? '');

if($key !== $apiKey)
{
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

if(empty($search))
{
    echo json_encode([
        "success" => false,
        "message" => "Search value required"
    ]);
    exit;
}

$sql = "
SELECT TOP 1
CM_CODE,
CM_TITLE,
CM_NAME,
CM_NIC,
CM_MOBILE,
CM_DOB,
CM_POINTS,
CM_ADD1,
CM_ADD2,
CM_ADD3,
CM_ADD4
FROM M_TBLCUSTOMERS
WHERE CM_CODE = ?
OR CM_MOBILE = ?
OR CM_NIC = ?
";

$params = [$search,$search,$search];

$stmt = sqlsrv_query($conn,$sql,$params);

if($stmt && $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC))
{
    if($row['CM_DOB'] instanceof DateTime)
    {
        $row['CM_DOB'] = $row['CM_DOB']->format('Y-m-d');
    }

    echo json_encode([
        "success" => true,
        "customer" => $row
    ]);
}
else
{
    echo json_encode([
        "success" => false,
        "message" => "Customer not found"
    ]);
}