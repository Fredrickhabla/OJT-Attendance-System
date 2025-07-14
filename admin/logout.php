<?php
session_start();
session_unset();
session_destroy();

header("Location: /ojtform/indexv2.php?logout=1");
exit;
