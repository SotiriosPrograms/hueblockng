<?php
// Note: Ce code suppose que le contrôleur a déjà défini les variables $blocks_sorted et $sprite_meta.
// Si vous utilisez le code du contrôleur ci-dessus, $blocks_data contient {blocks: [...], meta: [...]}.
$json_blocks_data = json_encode($blocks_sorted);
$json_sprite_meta = json_encode($sprite_meta);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GloryHueBlocks Generator</title>
    <meta name="description" content="Générateur de dégradés de blocs (HueBlocks) pour Minecraft et NationsGlory. Utilise l'algorithme Delta E 2000 pour une précision chromatique optimale.">
    <meta name="keywords" content="Minecraft, NationsGlory, HueBlocks, Gradient, Dégradé, Blocs, Delta E, Builder">
    
    </head>
<body>
    
    <div id="splashScreen" class="splash-screen">
        <div class="loader-content">
            <div class="spinner"></div>
            <p>Chargement des ressources initiales...</p>
        </div>
    </div>
    
    <div id="legalBanner" class="legal-banner" style="display: none;">
        <p>En utilisant ce site, vous acceptez les conditions d'utilisation. GloryHueBlocks n'est affilié à Minecraft, Mojang ou NationsGlory. (V1)</p>
        <button onclick="hideLegalBanner()">Compris</button>
    </div>
    
    <header class="app-header">
        <svg width="350" height="70" viewBox="0 0 350 70" xmlns="http://www.w3.org/2000/svg">
            <g transform="translate(10, 10)">
                <defs>
                    <linearGradient id="gradientHue" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#007acc"/>
                        <stop offset="50%" stop-color="#00a6e0"/>
                        <stop offset="100%" stop-color="#FFD700"/>
                    </linearGradient>
                    <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="2" result="blur"/>
                        <feMerge>
                            <feMergeNode in="blur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>
                <rect x="-2" y="-2" width="48" height="48" fill="url(#gradientHue)" opacity="0.13" rx="6"/>
                <rect x="0" y="22" width="16" height="16" fill="#007acc" rx="3"/>
                <rect x="14" y="11" width="16" height="16" fill="#008cd4" rx="3"/>
                <rect x="28" y="0" width="16" height="16" fill="#00a6e0" rx="3"/>
            </g>
            <text x="70" y="45" font-family="'Segoe UI', Arial, sans-serif" 
                  font-size="30" font-weight="800" letter-spacing="0.5"
                  filter="url(#softGlow)">
                <tspan fill="#007acc">Glory</tspan>
                <tspan fill="#f0f0f0">Hue</tspan>
                <tspan fill="#00a6e0">Blocks</tspan>
            </text>
        </svg>
    </header>
    
    <main class="app-container">
        
        <form method="POST" action="index.php" class="controls">
            
            <div class="control-group mode-selector-container">
                <label class="control-label">Mode de sélection :</label>
                <div class="mode-toggle">
                    <input type="radio" id="modeBlock" name="mode" value="block" onclick="toggleMode('block')" <?= $mode === 'block' ? 'checked' : '' ?>>
                    <label for="modeBlock" class="toggle-btn">Par Bloc</label>
                    
                    <input type="radio" id="modeHex" name="mode" value="hex" onclick="toggleMode('hex')" <?= $mode === 'hex' ? 'checked' : '' ?>>
                    <label for="modeHex" class="toggle-btn">Par Couleur Hex <span class="v1-tag">V1 (TEST)</span></label>
                </div>
            </div>

            <div class="main-gradient-container" style="display: <?= $mode === 'hex' ? 'block' : 'none' ?>;" id="hexControlBar">
                <div class="gradient-bar-display" style="background: linear-gradient(to right, <?= htmlspecialchars($startHex) ?>, <?= htmlspecialchars($endHex) ?>);">
                    
                    <div class="hex-selector left">
                        <input type="color" id="startHexPicker" value="<?= htmlspecialchars($startHex) ?>" oninput="updateHexDisplay('start', this.value)">
                        <input type="text" name="startHex" id="startHexInput" value="<?= htmlspecialchars($startHex) ?>" oninput="updateHexDisplay('start', this.value)" maxlength="7">
                    </div>
                    
                    <div class="hex-text-display">
                        <span id="startHexTextDisplay"><?= htmlspecialchars($startHex) ?></span> 
                        → 
                        <span id="endHexTextDisplay"><?= htmlspecialchars($endHex) ?></span>
                    </div>

                    <div class="hex-selector right">
                        <input type="color" id="endHexPicker" value="<?= htmlspecialchars($endHex) ?>" oninput="updateHexDisplay('end', this.value)">
                        <input type="text" name="endHex" id="endHexInput" value="<?= htmlspecialchars($endHex) ?>" oninput="updateHexDisplay('end', this.value)" maxlength="7">
                    </div>
                </div>
            </div>

            <div id="blockSelection" style="display: <?= $mode === 'block' ? 'flex' : 'none' ?>; flex-wrap: wrap; gap: 20px;">
                
                <div class="control-group block-select-group">
                    <label class="control-label">Bloc de Départ :</label>
                    <div class="block-visual-selector" onclick="openBlockSelector('start')">
                        <div id="startBlockVisual" class="selected-block-preview">
                            <?php if ($startKey): ?>
                                <?php endif; ?>
                        </div>
                        <button type="button" class="btn-select-block">Choisir un Bloc</button>
                    </div>
                    <input type="hidden" name="startBlock" id="startBlockKey" value="<?= htmlspecialchars($startKey) ?>">
                </div>
                
                <div class="control-group block-select-group">
                    <label class="control-label">Bloc d'Arrivée :</label>
                    <div class="block-visual-selector" onclick="openBlockSelector('end')">
                        <div id="endBlockVisual" class="selected-block-preview">
                            <?php if ($endKey): ?>
                                <?php endif; ?>
                        </div>
                        <button type="button" class="btn-select-block">Choisir un Bloc</button>
                    </div>
                    <input type="hidden" name="endBlock" id="endBlockKey" value="<?= htmlspecialchars($endKey) ?>">
                </div>

            </div>

            <div class="control-group steps-selector-group">
                <label for="steps" class="control-label">Nombre d'Étapes :</label>
                
                <div class="steps-toggle" id="stepsToggle">
                    <input type="radio" id="step10" name="steps_preset" value="10" onclick="setSteps(10)" <?= $steps == 10 ? 'checked' : '' ?>>
                    <label for="step10" class="toggle-btn">10 Blocs</label>

                    <input type="radio" id="step20" name="steps_preset" value="20" onclick="setSteps(20)" <?= $steps == 20 ? 'checked' : '' ?>>
                    <label for="step20" class="toggle-btn">20 Blocs</label>
                    
                    <input type="radio" id="stepCustom" name="steps_preset" value="custom" onclick="setSteps('custom')" <?= ($steps != 10 && $steps != 20) ? 'checked' : '' ?>>
                    <label for="stepCustom" class="toggle-btn">Personnalisé</label>
                </div>
                
                <input type="hidden" name="steps" id="hiddenStepsInput" value="<?= $steps ?>">

                <input type="number" id="customStepsInput" 
                       placeholder="Entrez le nombre (max 50)" 
                       value="<?= $steps ?>" 
                       min="2" max="50" 
                       style="display: none; margin-top: 10px;"
                       oninput="updateCustomSteps()">
            </div>
            
            <button type="button" class="btn-generate" onclick="submitFormAJAX(event)">Générer le Dégradé</button>
        </form>
        
        <hr class="separator">

        <h2 class="section-title">Résultat du Dégradé (<span id="blockCount"><?= count($gradientResult) ?></span> Blocs)</h2>
        <div id="gradientOutput" class="output-container">
            <?php if (!empty($gradientResult)): ?>
                <?php foreach ($gradientResult as $block): ?>
                    <?php 
                    $rgb = $block['rgb'];
                    $coords = $block['sprite_coords'];
                    $res = $block['resolution'];
                    $spriteFile = $block['sprite_image'];

                    $scaleFactor = 80 / $res;
                    
                    $infoString = htmlspecialchars(json_encode([
                        'name' => $block['name'],
                        'category' => $block['category'],
                        'rgb' => "({$rgb['r']}, {$rgb['g']}, {$rgb['b']})",
                        'deltaE' => $block['deltaE']
                    ]));
                    ?>
                    <div class="block-result" 
                         title="" 
                         data-block-info="<?= $infoString ?>"
                         onmouseover="showTooltip(event)"
                         onmouseout="hideTooltip()">
                        
                        <div class="block-sprite-preview <?= $res === 16 ? 'pixel-texture' : 'smooth-texture' ?>"
                             style="
                                background-image: url('public/textures/<?= $spriteFile ?>');
                                background-position: -<?= $coords['x'] * $scaleFactor ?>px -<?= $coords['y'] * $scaleFactor ?>px;
                                transform: scale(<?= $scaleFactor ?>);
                                width: <?= $res ?>px; 
                                height: <?= $res ?>px;
                             ">
                        </div>
                        
                        <span class="block-name"><?= htmlspecialchars($block['name']) ?></span>
                    </div>
                <?php endforeach; ?>
                
                <button type="button" class="btn-copy-sequence" onclick="copySequence()">Copier la Séquence</button>

            <?php else: ?>
                <p class="message-info">Sélectionnez vos couleurs ou blocs, puis cliquez sur "Générer le Dégradé".</p>
            <?php endif; ?>
        </div>

        <div id="customTooltip" class="custom-tooltip" style="display:none;">
            <p><strong>Nom:</strong> <span id="tooltipName"></span></p>
            <p><strong>Catégorie:</strong> <span id="tooltipCategory"></span></p>
            <p><strong>RVB:</strong> <span id="tooltipRgb"></span></p>
            <p><strong>Delta E:</strong> <span id="tooltipDeltaE"></span></p>
        </div>
        
    </main>

    <div id="blockSelectorModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-button" onclick="closeBlockSelector()">&times;</span>
            <h3 class="modal-title">Sélectionner un Bloc</h3>
            
            <div class="block-grid-container">
                <?php foreach ($blocks_sorted as $category => $blocks_in_category): ?>
                    <h4 class="category-title"><?= ucfirst($category) ?></h4>
                    <div class="category-grid">
                        <?php foreach ($blocks_in_category as $key => $block): ?>
                            <?php 
                            $res = $block['resolution'];
                            $coords = $block['sprite_coords'];
                            $spriteFile = $block['sprite_image'];
                            ?>
                            <div class="grid-block-item" 
                                 data-key="<?= $key ?>" 
                                 data-name="<?= htmlspecialchars($block['name']) ?>"
                                 data-res="<?= $res ?>" 
                                 data-x="<?= $coords['x'] ?>" 
                                 data-y="<?= $coords['y'] ?>"
                                 data-sprite-file="<?= $spriteFile ?>"
                                 onclick="selectBlock(this)">
                                
                                <div class="block-sprite-preview grid-item-preview <?= $res === 16 ? 'pixel-texture' : 'smooth-texture' ?>"
                                     style="
                                        /* La position de la texture sera mise à jour par JS */
                                        background-image: url('public/textures/<?= $spriteFile ?>');
                                        background-position: -<?= $coords['x'] ?>px -<?= $coords['y'] ?>px;
                                        background-size: <?= $sprite_meta[$spriteFile]['w'] ?>px <?= $sprite_meta[$spriteFile]['h'] ?>px; 
                                        width: <?= $res ?>px; 
                                        height: <?= $res ?>px;
                                        transform: scale(<?= 64 / $res ?>); /* Mise à l'échelle pour afficher 64px */
                                     ">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script>
        // CODE JAVASCRIPT INTÉGRAL (incluant le Throttling, le Sprite Sheet rendering et l'AJAX)
        const PHP_BLOCKS_DATA = <?= json_encode($blocks_sorted); ?>;
        
        let currentSelectionSide = '';
        const LOADING_DELAY_MS = 50; 
        let imageQueue = [];

        function processImageQueue() {
            if (imageQueue.length === 0) {
                return;
            }

            const lazyImage = imageQueue.shift(); 
            const realSrc = lazyImage.getAttribute('data-src');

            if (realSrc) {
                lazyImage.src = realSrc;
                lazyImage.removeAttribute('data-src'); 
            }

            setTimeout(processImageQueue, LOADING_DELAY_MS);
        }
        
        function initializeSequentialLoading() {
            const images = document.querySelectorAll('#blockSelectorModal .lazy-load-texture');
            
            if (imageQueue.length === 0) {
                imageQueue = Array.from(images).filter(img => img.getAttribute('data-src'));
                processImageQueue(); 
            }
        }
        
        function toggleMode(mode) {
            document.getElementById('blockSelection').style.display = (mode === 'block' ? 'flex' : 'none');
            document.getElementById('hexControlBar').style.display = (mode === 'hex' ? 'block' : 'none');
        }

        function updateHexDisplay(side, hex) {
            if (!/^#([0-9A-F]{3}){1,2}$/i.test(hex)) return;

            const isStart = side === 'start';
            const picker = document.getElementById(isStart ? 'startHexPicker' : 'endHexPicker');
            const input = document.getElementById(isStart ? 'startHexInput' : 'endHexInput');
            const textDisplay = document.getElementById(isStart ? 'startHexTextDisplay' : 'endHexTextDisplay');

            picker.value = hex.toUpperCase();
            input.value = hex.toUpperCase();
            textDisplay.textContent = hex.toUpperCase();

            const start = document.getElementById('startHexInput').value;
            const end = document.getElementById('endHexInput').value;
            document.querySelector('.gradient-bar-display').style.background = `linear-gradient(to right, ${start}, ${end})`;
        }

        function setSteps(value) {
            const customInput = document.getElementById('customStepsInput');
            const hiddenInput = document.getElementById('hiddenStepsInput');
            
            if (value === 'custom') {
                customInput.style.display = 'block';
                hiddenInput.value = customInput.value; 
            } else {
                customInput.style.display = 'none';
                hiddenInput.value = value;
            }
        }
        
        function updateCustomSteps() {
            const customInput = document.getElementById('customStepsInput');
            const hiddenInput = document.getElementById('hiddenStepsInput');
            
            let val = parseInt(customInput.value);
            if (isNaN(val) || val < 2) val = 2;
            if (val > 50) val = 50;
            
            customInput.value = val;
            hiddenInput.value = val;
        }


        // --- LOGIQUE AJAX DE SOUMISSION ---
        function submitFormAJAX(event) {
            event.preventDefault(); 
            
            const outputContainer = document.getElementById('gradientOutput');
            const form = document.querySelector('.controls');
            const blockCountSpan = document.getElementById('blockCount');

            // 1. Afficher l'écran de chargement
            outputContainer.innerHTML = '<p class="loading-message">🚀 Calcul du dégradé en cours... Ceci peut prendre quelques secondes.</p>';
            blockCountSpan.textContent = '...';
            
            // 2. Préparer les données
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => data[key] = value);
            
            if (data.steps_preset === 'custom') {
                data.steps = document.getElementById('customStepsInput').value;
            }

            // 3. Appel AJAX au nouveau point d'entrée PHP (ajax_generate.php)
            fetch('ajax_generate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(data => {
                
                if (data.success && data.gradient.length > 0) {
                    // 4. Succès : Afficher les nouveaux blocs
                    displayGradientResults(data.gradient);
                    blockCountSpan.textContent = data.gradient.length;
                } else {
                    // 5. Échec
                    outputContainer.innerHTML = '<p class="error-message">❌ Erreur de génération. Vérifiez les sélections.</p>';
                    blockCountSpan.textContent = '0';
                }
            })
            .catch(error => {
                outputContainer.innerHTML = '<p class="error-message">⚠️ Connexion perdue ou Time-out serveur. Veuillez réessayer.</p>';
                blockCountSpan.textContent = '0';
                console.error('AJAX Error:', error);
            });
        }

        function displayGradientResults(gradientArray) {
            const outputContainer = document.getElementById('gradientOutput');
            let htmlContent = '';
            
            gradientArray.forEach(block => {
                const infoString = JSON.stringify({
                    'name': block.name,
                    'category': block.category,
                    'rgb': `(${block.rgb.r}, ${block.rgb.g}, ${block.rgb.b})`,
                    'deltaE': block.deltaE
                });

                const res = block.resolution;
                const coords = block.sprite_coords;
                const spriteFile = block.sprite_image;
                const scaleFactor = 80 / res; // Scale pour aller à 80px
                
                htmlContent += `
                    <div class="block-result" 
                         data-block-info='${infoString}'
                         onmouseover="showTooltip(event)"
                         onmouseout="hideTooltip()">
                        
                        <div class="block-sprite-preview ${res === 16 ? 'pixel-texture' : 'smooth-texture'}"
                            style="
                                background-image: url('public/textures/${spriteFile}');
                                background-position: -${coords.x * scaleFactor}px -${coords.y * scaleFactor}px;
                                transform: scale(${scaleFactor});
                                width: ${res}px; 
                                height: ${res}px;
                             ">
                        </div>
                    </div>
                `;
            });
            
            htmlContent += '<button type="button" class="btn-copy-sequence" onclick="copySequence()">Copier la Séquence</button>';

            outputContainer.innerHTML = htmlContent;
        }

        // --- Fonctions de Modale/Tooltip/Copy (inchangées) ---
        function openBlockSelector(side) {
            currentSelectionSide = side;
            document.getElementById('blockSelectorModal').style.display = 'block';
            initializeSequentialLoading(); 
        }

        function closeBlockSelector() {
            document.getElementById('blockSelectorModal').style.display = 'none';
        }

        function selectBlock(blockElement) {
            const key = blockElement.getAttribute('data-key');
            const res = blockElement.getAttribute('data-res');
            const spriteFile = blockElement.getAttribute('data-sprite-file');
            const x = blockElement.getAttribute('data-x');
            const y = blockElement.getAttribute('data-y');
            
            const scaleFactor = 80 / res; // Scale pour aller à 80px (Taille de l'aperçu)

            // 1. Rendu du bloc dans la prévisualisation
            const previewDiv = document.getElementById(currentSelectionSide + 'BlockVisual');
            previewDiv.innerHTML = `
                <div class="block-sprite-preview ${res == 16 ? 'pixel-texture' : 'smooth-texture'}"
                    style="
                        background-image: url('public/textures/${spriteFile}');
                        background-position: -${x * scaleFactor}px -${y * scaleFactor}px;
                        transform: scale(${scaleFactor});
                        width: ${res}px; 
                        height: ${res}px;
                    ">
                </div>
            `;
            
            // 2. Mettre à jour le champ caché
            document.getElementById(currentSelectionSide + 'BlockKey').value = key;

            closeBlockSelector();
        }
        
        // ... (Le reste des fonctions JS : showTooltip, hideTooltip, copySequence, legalBanner, etc.) ...
        function showTooltip(event) {
            const blockElement = event.currentTarget;
            const infoString = blockElement.getAttribute('data-block-info');
            
            if (!infoString) return;

            const info = JSON.parse(infoString);
            
            tooltipName.textContent = info.name;
            tooltipCategory.textContent = info.category;
            tooltipRgb.textContent = info.rgb;
            tooltipDeltaE.textContent = parseFloat(info.deltaE).toFixed(2);
            
            const rect = blockElement.getBoundingClientRect();
            
            tooltip.style.left = (rect.left + window.scrollX + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            tooltip.style.top = (rect.top + window.scrollY - tooltip.offsetHeight - 10) + 'px'; 
            
            tooltip.style.display = 'block';
        }

        function hideTooltip() {
            tooltip.style.display = 'none';
        }

        function copySequence() {
            const blockElements = document.querySelectorAll('#gradientOutput .block-result');
            let sequence = '';
            
            blockElements.forEach(element => {
                const infoString = element.getAttribute('data-block-info');
                if (infoString) {
                    const info = JSON.parse(infoString);
                    sequence += `${info.category.toLowerCase()}:${info.name} `; 
                }
            });

            if (sequence.trim().length > 0) {
                navigator.clipboard.writeText(sequence.trim())
                    .then(() => {
                        alert('Séquence de blocs copiée dans le presse-papiers !');
                    })
                    .catch(err => {
                        console.error('Erreur lors de la copie :', err);
                        alert('Impossible de copier la séquence.');
                    });
            } else {
                 alert('Aucun bloc à copier.');
            }
        }
        
        function showLegalBanner() {
            if (localStorage.getItem('gloryhueblocks_legal_accepted') !== 'true') {
                document.getElementById('legalBanner').style.display = 'flex';
            }
        }

        function hideLegalBanner() {
            localStorage.setItem('gloryhueblocks_legal_accepted', 'true');
            document.getElementById('legalBanner').style.display = 'none';
        }

        function initializeBlockSelection() {
            const startKey = document.getElementById('startBlockKey').value;
            const endKey = document.getElementById('endBlockKey').value;
            
            // Reconstruit l'affichage des prévisualisations au démarrage
            const initializeVisuals = () => {
                const updateVisual = (key, visualId) => {
                    if (!key) return;
                    const item = document.querySelector(`.grid-block-item[data-key="${key}"]`);
                    if (item) {
                        const previewDiv = document.getElementById(visualId);
                        const res = item.getAttribute('data-res');
                        const spriteFile = item.getAttribute('data-sprite-file');
                        const x = item.getAttribute('data-x');
                        const y = item.getAttribute('data-y');
                        const scaleFactor = 44 / res; // Scale pour aller à 44px (Taille de l'aperçu)

                        previewDiv.innerHTML = `
                             <div class="block-sprite-preview ${res == 16 ? 'pixel-texture' : 'smooth-texture'}"
                                style="
                                    background-image: url('public/textures/${spriteFile}');
                                    background-position: -${x * scaleFactor}px -${y * scaleFactor}px;
                                    transform: scale(${scaleFactor});
                                    width: ${res}px; 
                                    height: ${res}px;
                                ">
                            </div>
                        `;
                    }
                };
                
                updateVisual(startKey, 'startBlockVisual');
                updateVisual(endKey, 'endBlockVisual');
            };

            setTimeout(initializeVisuals, 100); 
        }

        document.addEventListener('DOMContentLoaded', () => {
            
            // 1. Démarrer le chargement des 400 textures en arrière-plan (séquentiellement)
            initializeSequentialLoading(); 

            // 2. Retarder les actions d'interface pour l'UX du Splash Screen
            setTimeout(() => {
                
                // Masquer l'écran de chargement
                const splash = document.getElementById('splashScreen');
                splash.style.opacity = '0';
                setTimeout(() => splash.style.display = 'none', 500); 

                // Initialisation de l'UI
                const modeInput = document.querySelector('input[name="mode"]:checked');
                if (modeInput) {
                    toggleMode(modeInput.value);
                }
                
                const initialSteps = parseInt(document.getElementById('hiddenStepsInput').value);
                if (initialSteps !== 10 && initialSteps !== 20) {
                    document.getElementById('customStepsInput').style.display = 'block';
                }
                
                initializeBlockSelection(); 
                showLegalBanner(); 
                
            }, 800); // 🛑 TEMPS DE RETARD (800ms)
        });
    </script>
</body>
</html>
