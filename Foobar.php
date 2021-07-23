<?php

$count = 1;
while($count <= 100) {
    echo $count;
    if($count % 3 == 0 && $count % 5 == 0) {
        echo ' foobar';
    } elseif($count % 3 == 0) {
        echo ' foo';
    } elseif($count % 5 == 0) {
        echo ' bar';
    }
    echo "\n";
    $count++;
}
