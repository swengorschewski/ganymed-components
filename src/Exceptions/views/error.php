<!doctype html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Ganymed - Error</title>
</head>
<body>

<div class="content">

    <link href='http://fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <style>
        body {
            position: absolute;
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
        }

        .center {
            position: absolute;
            width: 100%;
            min-height: 100%;
            background-color: #E4E4E4;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-align-items: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-justify-content: center;
            -ms-flex-pack: center;
            justify-content: center;
        }

        .error {
            width: 60%;
            max-width: 40em;
            color: #6b6b6b;
        }

        h1 {
            font-size: 2.4em;
            margin: 0 0 0.5em 0;
            font-family: 'Roboto', sans-serif;
            font-weight: 300;
        }

        h2 {
            font-size: 1.4em;
            margin: 1em 0 0.5em 0;
            font-family: 'Roboto', sans-serif;
            font-weight: 300;
        }
    </style>


    <div class="center">
        <div class="error">
            <h2><?php echo (new \ReflectionClass($exception))->getShortName(); ?></h2>
            <h1><?php echo $exception->getMessage(); ?></h1>
            <ul>
                <?php
                    pd($exception->getFile());
                    foreach($exception->getTrace() as $trace) {
                        echo '<li>';
                        pd($trace);
                        echo '</li>';
                    }
                ?>
            </ul>
        </div>
    </div>

</div>

</body>
</html>