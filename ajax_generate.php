<?php
// ajax_generate.php (À placer à la racine du projet, à côté de index.php)

// Augmente légèrement le temps d'exécution PHP pour éviter le Time-out du côté serveur
set_time_limit(60); 

// Configure le header pour retourner du JSON
header('Content-Type: application/json');

// Inclure le Modèle
require_once 'app/models/HueBlockModel.php'; 

// Récupère les données POST brutes
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data provided or invalid JSON.']);
    exit;
}

try {
    $model = new HueBlockModel();
    
    // Récupère les paramètres du formulaire
    $mode = $data['mode'] ?? 'block';
    $steps = (int)($data['steps'] ?? 10);
    
    if ($mode === 'hex') {
        $startHex = $data['startHex'] ?? '#ffffff';
        $endHex = $data['endHex'] ?? '#000000';
        $result = $model->generateGradientFromHex($startHex, $endHex, $steps);
    } else {
        $startKey = $data['startBlock'] ?? '';
        $endKey = $data['endBlock'] ?? '';
        $result = $model->generateGradient($startKey, $endKey, $steps);
    }

    // Retourne le résultat du dégradé
    echo json_encode(['success' => true, 'gradient' => $result]);
    
} catch (Exception $e) {
    // Message général pour des raisons de sécurité
    error_log("GloryHueBlocks Calculation Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Calculation Error. Please try again.']);
}
?>
