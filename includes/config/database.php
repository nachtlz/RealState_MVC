<?php

function conectarBD() : mysqli {
    $db = new mysqli("localhost", "root", "root", "bienesraices_crud");

    if (!$db) {
        echo "Error: Unable to establish a connection with the database.";
        exit;
    }

    return $db;
}