<?php

define("ABSOLUTE_ROOT_PATH", __DIR__);
var_dump(ABSOLUTE_ROOT_PATH);
die(var_dump(__DIR__));

require "lib/Router.php";
require "lib/Kernel.php";
require "lib/Database.php";
require "lib/Flashbag.php";

$kernel = new Kernel();
$kernel->bootstrap();
$kernel->run();
