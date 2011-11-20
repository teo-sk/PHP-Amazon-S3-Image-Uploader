<?php

include ("./inc/init.php");
?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Ftp</title>
        <meta name="description" content="Uploader">
        <meta name="author" content="Matej Teo Zilak">
    </head>
    <body>
        <div id="container">
            <header>
                <h1>FTP</h1>
                <p>
                    Commands that can be used via Uploader class for CDN browsing:
                </p>
                <ul>
                    <li>
                        "cd dirname" enter the directory with given name. "cd .." for dir up
                    </li>
                    <li>
                        "ls" list files in a dir
                    </li>
                    <li>
                        "mkdir dirname" make a directory
                    </li>
                    <li>
                        "rm filename" remove a filename - for dir removal "rm dirname/" also with ending slash
                    </li>
                    <li>
                        "link filename" get link to the file
                    </li>
                </ul>
            </header>
            <div id="main" role="main">
                <form method="post" action="ftp.php">
                    <input id="console" name="console" type="text" />
                    <input type="submit" name="go" value="go" />
                </form>
            </div>
            <footer>
                <div id="console_out">
                    <?php
                    if (isset($_POST['go'])) {
                        $ftp = new Uploader(0);
                        if (isset($_COOKIE['Uploader_dir'])) {
                            $ftp -> setActualDir($_COOKIE['Uploader_dir']);
                        }
                        $action_dir = explode(" ", $_POST['console']);
                        $method = $action_dir[0];
                        $dir = $action_dir[1];
                        switch ($method) {
                            case "ls" :
                                $ftp -> ls();
                                break;
                            case "cd" :
                                $ftp -> cd($dir);
                                break;
                            case "mkdir" :
                                $ftp -> mkdir($dir);
                                break;
                            case "rm" :
                                $ftp -> rm($dir);
                                break;
                            case "link" :
                                $ftp -> getLink($dir);
                                break;
                            default :
                                echo "Error";
                                break;
                        }

                    }
                    ?>
                </div>
            </footer>
        </div>
    </body>
</html>