<?php

echo "<pre>";
print_r($_POST); print("\n");
$a = json_decode($_POST);
if ($a === false) print("false\n");
print("id = ".$a["id"]."\n");
print("nom = ".$a["nom"]."\n");
echo "</pre>";



