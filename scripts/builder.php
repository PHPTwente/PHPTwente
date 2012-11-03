<?php

require_once 'class.WebsiteBuilder.php';

$sProjectRoot = realpath(__DIR__ . '/../');

$oWebsiteBuilder = new WebsiteBuilder();
$oWebsiteBuilder->setTemplateFile($sProjectRoot . '/index.html');
$oWebsiteBuilder->setInputDirectory($sProjectRoot . '/_/html/');
$oWebsiteBuilder->setOutputDirectory($sProjectRoot . '/pages');
$oWebsiteBuilder->run();

#EOF