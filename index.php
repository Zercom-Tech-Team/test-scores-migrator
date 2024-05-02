<?php
require_once __DIR__ . "/core/HandleCsv.php";
require_once __DIR__ . "/core/UserTestScore.php";

use Core\HandleCsv;
use Core\UserTestScore;

ini_set("display_errors", "on");
error_reporting(1);

const LMS = true;
const IN_FORMA = true;
const IS_API = true;
const _API_DEBUG = true;
const _deeppath_ = "../";
require __DIR__ . "/../base.php";

require_once _base_ . "/vendor/autoload.php";
require_once _base_ . "/lib/lib.template.php";

// initialize
require _base_ . "/lib/lib.bootstrap.php";
Boot::init(CHECK_SYSTEM_STATUS);

$file = _base_ . "/testCompletion/UserTestScores.csv";
$handleCsv = new HandleCsv($file);
$data = $handleCsv->readCSVFile();

$firstFiveItems = array_slice($data, 0, 5);
$count = 1;

foreach ($firstFiveItems as $item) {
    $userTestScore = new UserTestScore($item);
    $userTestScore->handle();

    print "<pre>";
    echo "Processed: " . $count;
    print_r($item);
    print "</pre>";

    $count++;
}
