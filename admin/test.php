<?php 
$name ="Danica";
$reversed ="";
for ($i = strlen($name) - 1; $i >= 0; $i--) {
    $reversed .= $name[$i];
}
echo "Reversed name: " . $reversed; // Output: "acinaD"
echo "<br>";