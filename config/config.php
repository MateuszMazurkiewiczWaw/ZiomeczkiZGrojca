<?php
ob_start(); //output buffering
session_start();

$timezone = date_default_timezone_set("Europe/Warsaw");

$con = mysqli_connect("localhost", "root", "", "social_ziomeczki_z_grojca");

if (mysqli_connect_errno()) {
    echo "Blad polaczenia z baza danych: " . mysqli_connect_errno();
}



?>