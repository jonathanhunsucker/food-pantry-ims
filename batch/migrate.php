<?php

$models = array(
    "Bag"
);

foreach ($models as $model_name) {
    $model_name::unmigrate();
    $model_name::migrate();
}

?>
