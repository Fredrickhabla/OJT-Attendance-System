<?php
$reversed = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];


    $chars = str_split($name);

    for ($i = count($chars) - 1; $i >= 0; $i--) {
        $reversed .= $chars[$i];
    }
}
?>

<form method="POST">
    <label>Enter your name:</label>
    <input type="text" name="name" required>
    <button type="submit">Reverse</button>
</form>

<?php if ($reversed): ?>
    <p><strong>Reversed name:</strong> <?= htmlspecialchars($reversed) ?></p>
<?php endif; ?>
