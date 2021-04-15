<?php
// Verbindung mit der Datenbank aufbauen
require_once "config.php";

$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["username"]))) {
        $username_err = "Bitte geben Sie einen Usernamen ein.";
    } else {
        $sql = "SELECT id FROM users WHERE username = '{$_POST["username"]}'";
        $result = pg_query($dbconn, $sql) or die("Die Anfrage ist fehlgeschlagen: " . pg_last_error());

        if (pg_num_rows($result) > 0) {
            $username_err = "Dieser Benutzername ist nicht verfügbar.";
        } else {
            $username = trim($_POST["username"]);
        }
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Bitte geben Sie ein Passwort ein.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Bitte geben sie ihr Passwort erneut ein.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Die Passwörter stimmen nicht überein";
        }
    }

    if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password) VALUES ('{$username}', '{$password_hash}')";
        if (pg_query($dbconn, $sql)) {
            header("location: login.php");
        } else {
            die("Insert fehlgeschlagen: " . pg_last_error());
        }
    }

    if (!empty($result)) {
        pg_free_result($result);
    }
    //Verbindung schließen
    pg_close($dbconn);
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src * 'unsafe-inline'">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 350px;
            padding: 20px;
        }
    </style>
    <title>Signup</title>
</head>

<body>
    <div class="wrapper">
        <h2>Registrieren</h2>
        <p>Bitte füllen Sie die Formulare aus, um einen Account zu erstellen</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" <?php echo (!empty($username_err)) ? 'is_invalid' : ''; ?>" value="<?php echo $username ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>
</body>

</html>