<?php

$string = file_get_contents("../txtml.xsd");

header("Content-type: application/xml");
echo $string;

?>