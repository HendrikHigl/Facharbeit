<?php
require_once "config.php";

session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: blog.php");
    exit;
}

$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Bitte Usernamen eingeben.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Bitte Passwort eingeben";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password FROM users WHERE username = '{$username}'";
        if ($result = pg_query($dbconn, $sql)) {
            if (pg_num_rows($result) == 1) {

                $result_arr = pg_fetch_assoc($result, 0);
                echo $result_arr["password"];
                if (password_verify($password, $result_arr["password"])) {
                    session_start();

                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $result_arr["id"];
                    $_SESSION["username"] = $result_arr["username"];

                    header("location: blog.php");
                } else {
                    $login_err = "Username oder Passwort ungültig";
                }
            } else {
                $login_err = "Username oder Passwort ungültig";
            }
        } else {
            echo "Ups. Da ist etwas schiefgegangen";
        }

        if ($result) pg_free_result($result);
    }
    pg_close($dbconn);
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
</head>

<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="index.php">Sign up now</a>.</p>
        </form>
    </div>
</body>

</html>