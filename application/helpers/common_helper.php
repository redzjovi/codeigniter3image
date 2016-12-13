<?php
function pr($data)
{
    if (is_array($data))
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
    else
    {
        var_dump($data);
    }
}
?>
