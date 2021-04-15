<?php


require_once "config.php";
// Initialize the session
session_start();

$script = 'alert("This is not an XSS-Attack")';
$hash = hash("sha256", $script);

function get_metadata($dbconn)
{
    $sql = "SELECT users.username, posts.created FROM posts INNER JOIN users ON users.id=posts.user_id";
    $result = pg_query($dbconn, $sql);
    if (pg_num_rows($result) > 0) {
        $raw = pg_fetch_all($result);
        foreach ($raw as $arr) {
            $names[] = $arr["username"];
            $dates[] = $arr["created"];
        }
    }
    return [$names, $dates];
}


function console_log($data)
{
    echo "<script>";
    echo "console.log(" . json_encode($data) . ")";
    echo "</script>";
}

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}


$posts = [];
$new_post = "";
$sql  = "SELECT * FROM posts";
$result = pg_query($dbconn, $sql);
if (pg_num_rows($result) > 0) {
    $posts = pg_fetch_all($result);
}

$names = $dates = [];
$names = get_metadata($dbconn)[0];
$dates = get_metadata($dbconn)[1];
$none = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (array_key_exists("new_post", $_POST)) {
        if (!empty(trim($_POST["new_post"]))) {
            //* Old, innocent Version
            $new_post = pg_escape_string(trim($_POST["new_post"]));

            //* Encoding
            //$new_post = htmlentities(trim($_POST["new_post"]));
            $user_id = $_SESSION["id"];
            $sql = "INSERT INTO posts (content, user_id) VALUES ('{$new_post}', '{$user_id}')";

            if (pg_query($dbconn, $sql)) {
                header("location: blog.php");
            } else {
                die("Insert fehlgeschlagen: " . pg_last_error());
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (array_key_exists("search", $_GET)) {
        console_log("Here 2");
        if (!empty(trim($_GET["search"]))) {
            $search = trim($_GET["search"]);
            console_log($search);
            $sql = "SELECT * FROM posts WHERE content ILIKE '%{$search}%' ";
            $result = pg_query($sql);
            if (pg_num_rows($result) > 0) {
                $none = false;
                $posts = pg_fetch_all($result);
                $names = get_metadata($dbconn)[0];
                $dates = get_metadata($dbconn)[1];
                //header("location: blog.php");
            } else {
                $posts = [];
                $dates = [];
                $none = true;
            }
        }
    }
}

pg_free_result($result);

pg_close($dbconn);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy" content="default-src  'self' ; script-src-elem  'self' 'unsafe-inline'; style-src 'unsafe-inline' *">
    <script type="text/javascript" src="./DOMPurify/dist/purify.min.js"></script>
    <title>Welcome</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
            text-align: center;
            display: grid;
            place-items: center;
        }

        .wrapper {
            width: 70vw;
            display: grid;
            place-items: center;
        }

        .list-group {
            display: flex;
            text-align: left;
        }

        .list-group-item {
            width: 30vw;
            height: 15vh;
            margin-bottom: 1em;
        }

        p.user {
            position: relative;
            top: 40%;
            left: 0%;
            color: black;
        }

        p.date {
            position: relative;
            top: 4%;
            left: 20vw;
        }

        .input-group {
            width: 16em;
            position: absolute;
            top: 1em;
            right: 1em;
        }

        .search-term {
            width: 80%;
            white-space: nowrap;
        }

        .term {
            font-size: 30px;
        }
    </style>
</head>

<body>
    <h1 class="my-5">Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to our site.</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="GET">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Search this blog" name="search">
            <div class="input-group-append">
                <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i></button>


            </div>
        </div>
    </form>
    <div class="wrapper">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
            <textarea class="form-control" name="new_post" id="post_field" cols="70"><?php if ($new_post) echo $new_post ?></textarea>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Posten">
            </div>
        </form>
        <div class="search-term">Sie haben nach <?php if (!empty($search)) echo "<div class='term' id='term'>{$search}</div>" ?> gesucht:</div><br>
        <div class="found"><?php if ($none) echo "Keine Posts vorhanden" ?></div>

       

        <div class="list-group">
            <?php
            for ($i = 0; $i < count($posts); $i++) {
                $time = strtotime($dates[$i]);
                $date = date("H:i, d.m.y", $time);
                echo "<div class='list-group-item'>";
                echo "<p class='content'></p>";
                echo "<p class='user'>{$names[$i]}</p>";
                echo "<p class='date'>$date</p>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <script>
        let posts = document.getElementsByClassName("content");
        let content;
        <?php 
            for ($i = 0; $i < count($posts); $i++) {
                $json_post = json_encode(json_encode($posts[$i]["content"]));
                echo "content = $json_post; \n";
                echo "posts[$i].innerHTML = DOMPurify.sanitize(JSON.parse(content)); \n";
            }
        ?>
    </script>

</body>

</html>