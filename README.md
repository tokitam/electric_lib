# electric_lib

Electric power infomartion library.

### Requirements
    PHP 5.3+

### LICENSE

Apache License Version 2.0

EXAMPLE
=========================

```
<?php
    require_once 'ElectricPower.php';

    // stop plan
    $saveing = ElectricPower::get('saving');
    
    $usage    = ElectricPower::get('usage');
    $capacity = ElectricPower::get('capacity');
    $rate     = ElectricPower::get('rate');


