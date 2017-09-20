<?php
$url = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\')."/logout.php";	
header("Location: ../logout.php");	
exit;