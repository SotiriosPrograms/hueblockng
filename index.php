<?php
// index.php (situé à la racine du projet)

// Afficher les erreurs PHP pour le développement
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once 'app/controllers/HueBlockController.php';

$controller = new HueBlockController();
$controller->index();