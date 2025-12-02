<?php

/**
 * Classe HueBlockModel
 * Gère le chargement des données de blocs, les conversions de couleurs (RVB -> Lab),
 * le calcul de la distance chromatique (Delta E 2000) et la logique de tri.
 */
class HueBlockModel {
    private $blockDatabase = [];
    private $dataFile = __DIR__ . '/../../data/hueblocks_ng_data.json'; 
    private $sprite_meta = []; 

    public function __construct() {
        if (!defined('M_PI')) {
            define('M_PI', pi());
        }
        
        $this->loadBlockData();
        $this->loadSpriteMetadata();
    }

    /**
     * NOUVEAU: CHARGER LES MÉTADONNÉES DES SPRITES
     * Charge les dimensions totales des Sprite Sheets pour le calcul du background-size.
     * Corrigé: Utilise des valeurs fixes basées sur la génération TexturePacker (64x1344 et 128x1344).
     */
    private function loadSpriteMetadata() {
        // Dimensions des Sprite Sheets
        $this->sprite_meta['sprites_16x16.png'] = ['w' => 64, 'h' => 1344];
        $this->sprite_meta['sprites_32x32.png'] = ['w' => 128, 'h' => 1344];
    }
    
    // --- Fonctions de Conversion RVB -> LAB ---
    
    private function toLinear($c) {
        return ($c > 0.04045) ? pow(($c + 0.055) / 1.055, 2.4) : $c / 12.92;
    }
    
    private function fThreshold($t) {
        return ($t > 0.008856) ? pow($t, 1/3) : (7.787 * $t + 16/116);
    }
    
    /**
     * Convertit une couleur RVB (0-255) en espace CIE Lab (D65).
     */
    private function rgbToLab($r, $g, $b) {
        $r /= 255.0; $g /= 255.0; $b /= 255.0;
        $r = $this->toLinear($r); $g = $this->toLinear($g); $b = $this->toLinear($b);

        $X = $r * 0.4124 + $g * 0.3576 + $b * 0.1805;
        $Y = $r * 0.2126 + $g * 0.7152 + $b * 0.0722;
        $Z = $r * 0.0193 + $g * 0.1192 + $b * 0.9505;

        $X /= 0.95047; $Y /= 1.00000; $Z /= 1.08883;

        $fx = $this->fThreshold($X); $fy = $this->fThreshold($Y); $fz = $this->fThreshold($Z);

        $L = 116.0 * $fy - 16.0;
        $a = 500.0 * ($fx - $fy);
        $b = 200.0 * ($fy - $fz);

        return ['L' => $L, 'a' => $a, 'b' => $b];
    }
    
    /**
     * Calcule la distance de couleur Delta E 2000 (CIE DE2000).
     */
    private function deltaE2000($lab1, $lab2) {
        $kL = 1.0; $kC = 1.0; $kH = 1.0;
        $L1 = $lab1['L']; $a1 = $lab1['a']; $b1 = $lab1['b'];
        $L2 = $lab2['L']; $a2 = $lab2['a']; $b2 = $lab2['b'];
        $C1 = sqrt($a1*$a1 + $b1*$b1);
        $C2 = sqrt($a2*$a2 + $b2*$b2);
        $L_bar = ($L1 + $L2) / 2.0;
        $C_bar = ($C1 + $C2) / 2.0;
        $a_prime_1 = $a1 + $a1/2.0 * (1.0 - sqrt(pow($C_bar, 7) / (pow($C_bar, 7) + 6103515625.0)));
        $a_prime_2 = $a2 + $a2/2.0 * (1.0 - sqrt(pow($C_bar, 7) / (pow($C_bar, 7) + 6103515625.0)));
        $C_prime_1 = sqrt(pow($a_prime_1, 2) + pow($b1, 2));
        $C_prime_2 = sqrt(pow($a_prime_2, 2) + pow($b2, 2));
        $h_prime_1 = atan2($b1, $a_prime_1);
        $h_prime_2 = atan2($b2, $a_prime_2);
        if ($h_prime_1 < 0) $h_prime_1 += 2.0 * M_PI;
        if ($h_prime_2 < 0) $h_prime_2 += 2.0 * M_PI;
        $h_prime_1 = $h_prime_1 * 180.0 / M_PI;
        $h_prime_2 = $h_prime_2 * 180.0 / M_PI;
        $dL_prime = $L2 - $L1;
        $dC_prime = $C_prime_2 - $C_prime_1;
        if (abs($h_prime_1 - $h_prime_2) <= 180.0) {
            $dH_prime = $h_prime_2 - $h_prime_1;
        } else {
            $dH_prime = ($h_prime_2 - $h_prime_1) > 0 ? ($h_prime_2 - $h_prime_1 - 360.0) : ($h_prime_2 - $h_prime_1 + 360.0);
        }
        $dH_prime = 2.0 * sqrt(max(0, $C_prime_1 * $C_prime_2)) * sin(deg2rad($dH_prime / 2.0));
        $L_bar = ($L1 + $L2) / 2.0;
        $C_prime_bar = ($C_prime_1 + $C_prime_2) / 2.0;
        if (abs($h_prime_1 - $h_prime_2) <= 180.0) {
            $h_bar = ($h_prime_1 + $h_prime_2) / 2.0;
        } else {
            $h_bar = ($h_prime_1 + $h_prime_2 + 360.0) / 2.0;
        }
        if ($C_prime_1 * $C_prime_2 === 0) $h_bar = $h_prime_1 + $h_prime_2;
        $T = 1.0 - 0.17 * cos(deg2rad($h_bar - 30.0)) + 0.24 * cos(deg2rad(2.0 * $h_bar)) + 0.32 * cos(deg2rad(3.0 * $h_bar + 6.0)) - 0.20 * cos(deg2rad(4.0 * $h_bar - 63.0));
        $SL = 1.0 + ((0.015 * pow($L_bar - 50.0, 2)) / sqrt(20.0 + pow($L_bar - 50.0, 2)));
        $SC = 1.0 + (0.045 * $C_prime_bar);
        $SH = 1.0 + (0.015 * $C_prime_bar * $T);
        $R_C = 2.0 * sqrt(pow($C_prime_bar, 7) / (pow($C_prime_bar, 7) + 6103515625.0));
        $d_theta = 30.0 * exp(-pow(($h_bar - 275.0) / 25.0, 2));
        $RT = -$R_C * sin(deg2rad(2.0 * $d_theta));
        return sqrt(
            pow($dL_prime / ($kL * $SL), 2) +
            pow($dC_prime / ($kC * $SC), 2) +
            pow($dH_prime / ($kH * $SH), 2) +
            $RT * ($dC_prime / ($kC * $SC)) * ($dH_prime / ($kH * $SH))
        );
    }
    
    // --- Fonctions de base (Data) ---
    private function loadBlockData() {
        if (file_exists($this->dataFile)) {
            $jsonContent = file_get_contents($this->dataFile);
            $data = json_decode($jsonContent, true);

            foreach ($data as $key => &$block) {
                if (!isset($block['lab'])) {
                     die("Erreur de données : Le bloc {$key} ne contient pas de données 'lab'. Veuillez relancer le script Python de fusion.");
                }
            }
            $this->blockDatabase = $data;

        } else {
            die("Erreur : Le fichier de données des blocs 'hueblocks_ng_data.json' n'a pas été trouvé ou est illisible. (Chemin testé: " . $this->dataFile . ")");
        }
    }

    public function getAllBlocks() {
        return $this->blockDatabase;
    }
    
    /**
     * Retourne les blocs triés par catégorie ET les métadonnées de la Sprite Sheet.
     */
    public function getBlocksByCategory() {
        $sorted = [];
        foreach ($this->blockDatabase as $key => $block) {
            if (isset($block['sprite_image'])) {
                $sorted[$block['category']][$key] = $block;
            }
        }
        ksort($sorted); 
        
        return ['blocks' => $sorted, 'meta' => $this->sprite_meta];
    }
    
    // --- Fonctions de génération de dégradé ---
    
    private function hexToRgb($hexColor) {
        $hexColor = ltrim($hexColor, '#');
        if (strlen($hexColor) !== 6) { return null; }
        return ['r' => hexdec(substr($hexColor, 0, 2)), 'g' => hexdec(substr($hexColor, 2, 2)), 'b' => hexdec(substr($hexColor, 4, 2)) ];
    }
    
    public function generateGradient($startKey, $endKey, $steps) {
        if (!isset($this->blockDatabase[$startKey]) || !isset($this->blockDatabase[$endKey])) {
            return [];
        }
        $startLab = $this->blockDatabase[$startKey]['lab'];
        $endLab = $this->blockDatabase[$endKey]['lab'];
        
        return $this->processGradient($startLab, $endLab, $steps);
    }
    
    public function generateGradientFromHex($startHex, $endHex, $steps) {
        $startRgb = $this->hexToRgb($startHex);
        $endRgb = $this->hexToRgb($endHex);

        if ($startRgb === null || $endRgb === null) {
            return [];
        }
        
        $startLab = $this->rgbToLab($startRgb['r'], $startRgb['g'], $startRgb['b']);
        $endLab = $this->rgbToLab($endRgb['r'], $endRgb['g'], $endRgb['b']);
        
        return $this->processGradient($startLab, $endLab, $steps);
    }
    
    /**
     * Coeur de l'algorithme : Interpolation Lab et recherche Delta E 2000 avec règles de priorité.
     */
    private function processGradient($startLab, $endLab, $steps) {
        $gradientBlocks = [];
        $steps = max(2, (int)$steps); 
        
        // --- LOGIQUE DE PRIORITÉ ROUGE PUR ---
        $PURE_RED_LAB = ['L' => 53.23, 'a' => 80.11, 'b' => 67.22]; 
        $PRIMARY_TARGETS = [['lab' => $PURE_RED_LAB, 'key' => 'minecraft_red_concrete', 'threshold_dE' => 8, 'max_dE_force' => 15 ]];
        // ---------------------------------------------

        for ($i = 0; $i < $steps; $i++) {
            $t = $i / ($steps - 1); 

            // Interpolation Lab
            $targetL = $startLab['L'] + ($endLab['L'] - $startLab['L']) * $t;
            $targetA = $startLab['a'] + ($endLab['a'] - $startLab['a']) * $t;
            $targetB = $startLab['b'] + ($endLab['b'] - $startLab['b']) * $t;
            $targetLab = ['L' => $targetL, 'a' => $targetA, 'b' => $targetB];
            
            $forced_key = null;
            $minDistance = INF;
            $bestKey = null;

            // VÉRIFICATION DES RÈGLES DE PRIORITÉ 
            foreach ($PRIMARY_TARGETS as $rule) {
                if ($this->deltaE2000($targetLab, $rule['lab']) < $rule['threshold_dE']) {
                    if (isset($this->blockDatabase[$rule['key']])) {
                        $distance_to_forced = $this->deltaE2000($targetLab, $this->blockDatabase[$rule['key']]['lab']);
                        if ($distance_to_forced < $rule['max_dE_force']) {
                            $forced_key = $rule['key'];
                            $minDistance = $distance_to_forced;
                            $bestKey = $forced_key;
                            break;
                        }
                    }
                }
            }

            // Recherche Lab normale
            if (!$bestKey) {
                foreach ($this->blockDatabase as $key => $block) {
                    $distance = $this->deltaE2000($targetLab, $block['lab']); 
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $bestKey = $key;
                    }
                }
            }
            
            if ($bestKey) {
                $bestBlock = $this->blockDatabase[$bestKey];
                $bestBlock['id'] = $bestKey; 
                $bestBlock['deltaE'] = round($minDistance, 2); 
                $gradientBlocks[] = $bestBlock;
            }
        }

        return $gradientBlocks;
    }
}
