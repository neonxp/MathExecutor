# NXP MathExecutor

Simple math expressions calculator

## Sample usage:

    <?php
    require "vendor/autoload.php";
    $calculator = new \NXP\MathExecutor();
    print $calculator->execute("1 + 2 * (2 - (4+10))^2");