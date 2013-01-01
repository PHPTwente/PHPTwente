<!DOCTYPE html>
<?php

    require_once 'class.WebsiteBuilder.php';

    $sProjectRoot = realpath(__DIR__ . '/../');

    $oWebsiteBuilder = new WebsiteBuilder();
    $oWebsiteBuilder->setProjectRoot($sProjectRoot);
    $oWebsiteBuilder->setTemplateFile($sProjectRoot . '/index.html');
    $oWebsiteBuilder->setInputDirectory($sProjectRoot . '/_/html/');
    $oWebsiteBuilder->setOutputDirectory($sProjectRoot . '/pages');
    $sOutput = $oWebsiteBuilder->run();
?>
<html>
<head>
    <meta charset="utf-8"/>
    <title>PHPTwente -- static site builder</title>
    <style>
        body {
            font-family: arial, sans-serif;
            font-size: 90%;
        }

        li {
            line-height: 1.5em;
        }

        li strong {
            color: white;
            padding: 0 0.5em;
            border-radius: 1em;
        }

        .succeeded {
            background-color: darkgreen;
        }

        .failed {
            background-color: darkred;
        }

        .error-message {
            color: red;
        }

        .filename {
            font-family: monospace;
            background-color: #EEF;
            border: 1px solid #99E;
            padding: 0.2em;
        }

    </style>
</head>

<body>
    <?=$sOutput?>
</body>
</html>