<?php
/**
 * Author: Alexander "NeonXP" Kiryukhin
 * Date: 14.03.13
 * Time: 1:08
 */
require "vendor/autoload.php";

$e = new \NXP\MathExecutor();

print $e->execute("1 + 2 * (2 - (4+10))^2");