<?php
header ( $_SERVER ["SERVER_PROTOCOL"] . " 404 Not Found", true, 404 );

?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
<title>404 Not Found</title>
</head>
<body>
	<h1>Not Found</h1>
	<p>The requested URL <?php print ($_SERVER['REQUEST_URI']);?> was not found on this server.</p>
	<hr>
	<address>Apache/2.4.18 (Ubuntu) Server at <?php print ($_SERVER['SERVER_NAME']);?>
		Port 80</address>
</body>
</html>


