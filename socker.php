<?php

$a = file_get_contents('e.tmp');
preg_match_all('/<tr><td>([\d\.]{4,12})<\/td><td>([\d]{1,10})<\/td>.+<\/tr>/Usi', $a, $matches);

