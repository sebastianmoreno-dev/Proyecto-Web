<?php

$db = mysqli_connect(
    "localhost",
    "estatearch",
    "123456",
    "estatearch"
);

if (!$db) {
    die("Error de conexión: " . mysqli_connect_error());
}

