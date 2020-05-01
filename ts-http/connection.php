<?php
    if (file_exists("settings.local.php")) {
        require("settings.local.php");
    }
    else {
        exit("Missing settings.local.php");
    }

    $dbConnection = new PDO("mysql:dbname={$db};host={$host};charset=utf8", $user, $password);
    $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>