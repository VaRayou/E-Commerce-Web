<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="POST" action="code.php">
        Name: <input type="text" name="fullname">
        <input type="submit" value="Submit">
    </form>
<?php
    $_SESSION["username"] = "Rotha-SETEC";
    echo "Session data saved.";
?>
</body>
</html>