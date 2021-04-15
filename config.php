<?php
$dbconn = pg_connect("host=localhost dbname=facharbeit user=hendrik password=hendrik") or die("Verbindung fehlgeschlagen: " . pg_last_error());
