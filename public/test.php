<?php
$str = $_GET['selector']??'div';
preg_match_all('/(^|\.|\#)([A-z0-9_\-]*)/i', $str, $match);
echo '<pre>';
print_r($match);