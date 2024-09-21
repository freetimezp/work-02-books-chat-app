<?php

//show array keys and values
function show($arr)
{
    foreach ($arr as $key => $value) {
        echo "<pre>" . $key . ": " . $value . "</pre>";
    }
}