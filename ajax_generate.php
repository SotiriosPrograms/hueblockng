<?php
/**
 * ajax_generate.php
 * Point d'entrée AJAX pour la génération de dégradés GloryHueBlocks
 * À placer à la racine du projet, à côté de index.php
 */

// Augmente le temps d'exécution PHP pour éviter le Time-out (60 secondes max)
set_time_limit(60);

// Headers de sécurité et performance
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Désactiver l'affichage des erreurs en production (les erreurs vont dans error_log)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Fonction helper pour retourner une réponse JSON
function jsonResponse($success, $data = [], $error = null) {
    echo json_encode([
        'success' => $success,
        'gradient' => $data,
        'error' => $error,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid request method. Use POST.');
}

// Récupère les données POST brutes
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

// Validation des données d'entrée
if (json_last_error() !== JSON_ERROR_NONE) {
    jsonResponse(false, [], 'Invalid JSON format: ' . json_last_error_msg());
}

if (!$data || !is_array($data)) {
    jsonResponse(false, [], 'No data provided or invalid format.');
}

try {
    // Inclure le Modèle
    require_once __DIR__ . '/app/models/HueBlockModel.php';
    
    $model = new HueBlockModel();
    
    // Récupère et valide les paramètres du formulaire
    $mode = isset($data['mode']) ? trim($data['mode']) : 'block';
    $steps = isset($data['steps']) ? (int)$data['steps'] : 10;
    
    // Validation du mode
    if (!in_array($mode, ['block', 'hex'], true)) {
        jsonResponse(false, [], 'Invalid mode. Must be "block" or "hex".');
    }
    
    // Validation du nombre d'étapes
    if ($steps < 2 || $steps > 50) {
        jsonResponse(false, [], 'Steps must be between 2 and 50.');
    }
    
    // Génération selon le mode sélectionné
    if ($mode === 'hex') {
        // Mode Hex : Validation des couleurs hexadécimales
        $startHex = isset($data['startHex']) ? trim($data['startHex']) : '#ffffff';
        $endHex = isset($data['endHex']) ? trim($data['endHex']) : '#000000';
        
        // Validation du format hex
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $startHex) || 
            !preg_match('/^#[0-9A-Fa-f]{6}$/', $endHex)) {
            jsonResponse(false, [], 'Invalid hex color format. Use #RRGGBB format.');
        }
        
        // Génération du dégradé hex
        $result = $model->generateGradientFromHex($startHex, $endHex, $steps);
        
        // Logging pour debug (optionnel)
        error_log("GloryHueBlocks: Hex gradient generated - {$startHex} to {$endHex} in {$steps} steps");
        
    } else {
        // Mode Block : Validation des clés de blocs
        $startKey = isset($data['startBlock']) ? trim($data['startBlock']) : '';
        $endKey = isset($data['endBlock']) ? trim($data['endBlock']) : '';
        
        // Vérification que les clés ne sont pas vides
        if (empty($startKey) || empty($endKey)) {
            jsonResponse(false, [], 'Start block and end block are required.');
        }
        
        // Génération du dégradé par blocs
        $result = $model->generateGradient($startKey, $endKey, $steps);
        
        // Logging pour debug (optionnel)
        error_log("GloryHueBlocks: Block gradient generated - {$startKey} to {$endKey} in {$steps} steps");
    }
    
    // Vérification que le résultat n'est pas vide
    if (empty($result)) {
        jsonResponse(false, [], 'No gradient could be generated. Check your input parameters.');
    }
    
    // 🔧 CORRECTION : S'assurer que tous les blocs ont bien le champ sprite_image
    // (normalement déjà géré par le modèle, mais double vérification)
    foreach ($result as &$block) {
        if (!isset($block['sprite_image'])) {
            $block['sprite_image'] = ($block['resolution'] == 16) 
                ? 'sprites_16x16.png' 
                : 'sprites_32x32.png';
        }
    }
    unset($block); // Libère la référence
    
    // Retourne le résultat avec succès
    jsonResponse(true, $result);
    
} catch (Exception $e) {
    // Log l'erreur complète côté serveur
    error_log("GloryHueBlocks Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Message générique pour le client (sécurité)
    jsonResponse(false, [], 'An error occurred during gradient calculation. Please try again.');
}
?>
