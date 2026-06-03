<?php

echo "<pre>";

echo "sqlsrv: ";
var_dump(extension_loaded('sqlsrv'));

echo "pdo_sqlsrv: ";
var_dump(extension_loaded('pdo_sqlsrv'));

?>