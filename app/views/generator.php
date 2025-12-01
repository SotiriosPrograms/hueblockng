<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GloryHueBlocks Generator</title>
    <meta name="description" content="Générateur de dégradés de blocs (HueBlocks) pour Minecraft et NationsGlory. Utilise l'algorithme Delta E 2000 pour une précision chromatique optimale.">
    <meta name="keywords" content="Minecraft, NationsGlory, HueBlocks, Gradient, Dégradé, Blocs, Delta E, Builder">
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    
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
                                <?php 
                                $startBlockData = reset($blocks_sorted); 
                                foreach ($blocks_sorted as $category => $blocks):
                                    if (isset($blocks[$startKey])):
                                        $startBlockData = $blocks[$startKey];
                                        $imgPath = 'public/textures/' . $startBlockData['category'] . '/' . $startBlockData['name'] . '.png';
                                        echo "<img src=\"{$imgPath}\" class=\"pixel-texture\" alt=\"{$startBlockData['name']}\">";
                                        break;
                                    endif;
                                endforeach;
                                ?>
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
                                <?php 
                                $endBlockData = reset($blocks_sorted); 
                                foreach ($blocks_sorted as $category => $blocks):
                                    if (isset($blocks[$endKey])):
                                        $endBlockData = $blocks[$endKey];
                                        $imgPath = 'public/textures/' . $endBlockData['category'] . '/' . $endBlockData['name'] . '.png';
                                        echo "<img src=\"{$imgPath}\" class=\"pixel-texture\" alt=\"{$endBlockData['name']}\">";
                                        break;
                                    endif;
                                endforeach;
                                ?>
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
            
            <button type="submit" class="btn-generate">Générer le Dégradé</button>
        </form>
        
        <hr class="separator">

        <h2 class="section-title">Résultat du Dégradé (<?= count($gradientResult) ?> Blocs)</h2>
        <div id="gradientOutput" class="output-container">
            <?php if (!empty($gradientResult)): ?>
                <?php foreach ($gradientResult as $block): ?>
                    <?php 
                    $rgb = $block['rgb'];
                    $imagePath = 'public/textures/' . $block['category'] . '/' . $block['name'] . '.png';
                    
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
                        
                        <img src="<?= $imagePath ?>" class="pixel-texture" alt="<?= htmlspecialchars($block['name']) ?>">
                        
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
                            <?php $imagePath = 'public/textures/' . $block['category'] . '/' . $block['name'] . '.png'; ?>
                            <div class="grid-block-item" 
                                 data-key="<?= $key ?>" 
                                 data-img="<?= $imagePath ?>"
                                 data-name="<?= htmlspecialchars($block['name']) ?>"
                                 onclick="selectBlock(this)">
                                
                                <img src="<?= $imagePath ?>" class="pixel-texture" alt="<?= htmlspecialchars($block['name']) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <script>
        let currentSelectionSide = '';
        
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


        // --- Fonctions de SÉLECTION DE BLOCS (Modale) ---

        function openBlockSelector(side) {
            currentSelectionSide = side;
            document.getElementById('blockSelectorModal').style.display = 'block';
        }

        function closeBlockSelector() {
            document.getElementById('blockSelectorModal').style.display = 'none';
        }

        function selectBlock(blockElement) {
            const key = blockElement.getAttribute('data-key');
            const imgPath = blockElement.getAttribute('data-img');
            const name = blockElement.getAttribute('data-name');

            // 1. Mettre à jour l'image de prévisualisation
            const previewDiv = document.getElementById(currentSelectionSide + 'BlockVisual');
            previewDiv.innerHTML = `<img src="${imgPath}" class="pixel-texture" alt="${name}">`;
            
            // 2. Mettre à jour le champ caché (pour la soumission du formulaire)
            document.getElementById(currentSelectionSide + 'BlockKey').value = key;

            // 3. Fermer la modale
            closeBlockSelector();
        }
        
        // --- Fonctions Tooltip ---
        const tooltip = document.getElementById('customTooltip');
        const tooltipName = document.getElementById('tooltipName');
        const tooltipCategory = document.getElementById('tooltipCategory');
        const tooltipRgb = document.getElementById('tooltipRgb');
        const tooltipDeltaE = document.getElementById('tooltipDeltaE');
        
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
        
        // --- NOUVELLE FONCTION DE COPIE ---
        function copySequence() {
            const blockElements = document.querySelectorAll('#gradientOutput .block-result');
            let sequence = '';
            
            blockElements.forEach(element => {
                const infoString = element.getAttribute('data-block-info');
                if (infoString) {
                    const info = JSON.parse(infoString);
                    // Format Minecraft: category:block_name
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
        // --- FIN FONCTION COPIE ---


        // --- NOUVELLES FONCTIONS LÉGALES ---
        function showLegalBanner() {
            if (localStorage.getItem('gloryhueblocks_legal_accepted') !== 'true') {
                document.getElementById('legalBanner').style.display = 'flex';
            }
        }

        function hideLegalBanner() {
            localStorage.setItem('gloryhueblocks_legal_accepted', 'true');
            document.getElementById('legalBanner').style.display = 'none';
        }
        // --- FIN FONCTIONS LÉGALES ---


        // --- Initialisation au Chargement ---
        function initializeBlockSelection() {
            // Logique pour s'assurer que si des clés sont déjà dans le formulaire,
            // l'image de prévisualisation s'affiche au démarrage.
            const startKey = document.getElementById('startBlockKey').value;
            const endKey = document.getElementById('endBlockKey').value;
            
            // On ne peut initialiser les images que si la modale a les blocs chargés (ce qui est le cas ici)
            const initializeVisuals = () => {
                const itemStart = document.querySelector(`.grid-block-item[data-key="${startKey}"]`);
                if (itemStart) {
                    const previewDiv = document.getElementById('startBlockVisual');
                    const imgPath = itemStart.getAttribute('data-img');
                    const name = itemStart.getAttribute('data-name');
                    previewDiv.innerHTML = `<img src="${imgPath}" class="pixel-texture" alt="${name}">`;
                }

                const itemEnd = document.querySelector(`.grid-block-item[data-key="${endKey}"]`);
                if (itemEnd) {
                    const previewDiv = document.getElementById('endBlockVisual');
                    const imgPath = itemEnd.getAttribute('data-img');
                    const name = itemEnd.getAttribute('data-name');
                    previewDiv.innerHTML = `<img src="${imgPath}" class="pixel-texture" alt="${name}">`;
                }
            };

            // On attend que le DOM soit complètement construit pour trouver les éléments
            setTimeout(initializeVisuals, 100); 
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Définit le mode par défaut au chargement (Blocs)
            if (!document.querySelector('input[name="mode"]:checked')) {
                document.getElementById('modeBlock').checked = true;
            }
            toggleMode(document.querySelector('input[name="mode"]:checked').value);
            
            // Affiche l'input custom si nécessaire
            const initialSteps = parseInt(document.getElementById('hiddenStepsInput').value);
            if (initialSteps !== 10 && initialSteps !== 20) {
                document.getElementById('customStepsInput').style.display = 'block';
            }
            
            // Initialisation des images et affichage du bandeau
            initializeBlockSelection(); 
            showLegalBanner(); 
        });
    </script>
</body>
</html>