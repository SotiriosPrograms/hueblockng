<?php

require_once __DIR__ . '/../models/HueBlockModel.php';

class HueBlockController {
    private $model;

    public function __construct() {
        $this->model = new HueBlockModel();
    }

    public function index() {
        $blocks_sorted = $this->model->getBlocksByCategory(); 
        $gradientResult = []; 
        $startKey = '';
        $endKey = '';
        $startHex = '#00bcd4'; 
        $endHex = '#ff7f50';   
        $mode = 'block';       // MODE BLOC PAR DÉFAUT
        $steps = 10;
        $steps_preset = '10';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mode = filter_input(INPUT_POST, 'mode', FILTER_SANITIZE_STRING) ?: 'block';
            
            $steps = filter_input(INPUT_POST, 'steps', FILTER_VALIDATE_INT);
            $steps_preset = filter_input(INPUT_POST, 'steps_preset', FILTER_SANITIZE_STRING) ?: 'custom';

            if ($mode === 'hex') {
                $startHex = filter_input(INPUT_POST, 'startHex', FILTER_SANITIZE_STRING);
                $endHex = filter_input(INPUT_POST, 'endHex', FILTER_SANITIZE_STRING);
                
                if (preg_match('/^#[a-f0-9]{6}$/i', $startHex) && preg_match('/^#[a-f0-9]{6}$/i', $endHex) && $steps >= 2) {
                    $gradientResult = $this->model->generateGradientFromHex($startHex, $endHex, $steps);
                }
            } else { 
                $startKey = filter_input(INPUT_POST, 'startBlock', FILTER_DEFAULT); 
                $endKey = filter_input(INPUT_POST, 'endBlock', FILTER_DEFAULT);
                $startKey = is_string($startKey) ? trim($startKey) : '';
                $endKey = is_string($endKey) ? trim($endKey) : '';
                
                if ($startKey && $endKey && $steps >= 2) {
                    $gradientResult = $this->model->generateGradient($startKey, $endKey, $steps);
                }
            }
        }
        
        require_once __DIR__ . '/../views/generator.php';
    }
}