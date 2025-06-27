<?php
session_start();
session_unset();
session_destroy();
header("Location: indexv2.php");
exit;
?>
