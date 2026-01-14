<?php
/**
 * SYSTEM VERIFICATION SCRIPT
 * Run this to verify the Product Collection Scraper is properly installed
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>System Verification - Product Collection Scraper</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        .check-section {
            margin: 20px 0;
            padding: 20px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #4299e1;
        }
        .check-section h2 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .check-item {
            padding: 12px;
            margin: 8px 0;
            background: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #e2e8f0;
        }
        .status {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }
        .status.success { background: #48bb78; }
        .status.error { background: #f56565; }
        .status.warning { background: #ed8936; }
        .check-item .label {
            flex: 1;
            color: #2d3748;
        }
        .check-item .value {
            color: #718096;
            font-size: 0.9rem;
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: center;
        }
        .summary h2 {
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        .summary .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .stat {
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            background: #4299e1;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover {
            background: #3182ce;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4);
        }
        .btn.secondary {
            background: #718096;
        }
        .btn.secondary:hover {
            background: #4a5568;
        }
        code {
            background: #edf2f7;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #e53e3e;
            font-size: 0.9rem;
        }
        .details {
            margin-top: 10px;
            padding: 10px;
            background: #edf2f7;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #4a5568;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 System Verification</h1>
    <p class="subtitle">Product Collection Scraper - Installation Check</p>

    <?php
    $checks = [
        'files' => [],
        'database' => [],
        'config' => []
    ];
    
    $totalChecks = 0;
    $passedChecks = 0;
    $warnings = 0;
    
    // ===================================
    // FILE CHECKS
    // ===================================
    echo '<div class="check-section">';
    echo '<h2>📁 File System Checks</h2>';
    
    $requiredFiles = [
        'Migration SQL' => __DIR__ . '/migration_product_collections.sql',
        'Setup Script' => __DIR__ . '/setup_product_collections.php',
        'Main Tool' => __DIR__ . '/../fetch_product_collection.php',
        'Database Helper' => __DIR__ . '/../inc/db.php',
        'Full Documentation' => __DIR__ . '/PRODUCT_COLLECTION_SCRAPER_DOCS.md',
        'Quick Start Guide' => __DIR__ . '/README_COLLECTION_SCRAPER.md',
        'Architecture Diagram' => __DIR__ . '/ARCHITECTURE_DIAGRAM.md',
    ];
    
    foreach ($requiredFiles as $name => $path) {
        $totalChecks++;
        $exists = file_exists($path);
        if ($exists) $passedChecks++;
        
        echo '<div class="check-item">';
        echo '<div class="status ' . ($exists ? 'success' : 'error') . '">';
        echo $exists ? '✓' : '✗';
        echo '</div>';
        echo '<div class="label">' . htmlspecialchars($name) . '</div>';
        echo '<div class="value">' . ($exists ? 'Found' : 'Missing') . '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // ===================================
    // DATABASE CHECKS
    // ===================================
    echo '<div class="check-section">';
    echo '<h2>🗄️ Database Checks</h2>';
    
    try {
        require __DIR__ . '/../inc/db.php';
        $pdo = getPDO();
        
        // Check connection
        $totalChecks++;
        $passedChecks++;
        echo '<div class="check-item">';
        echo '<div class="status success">✓</div>';
        echo '<div class="label">Database Connection</div>';
        echo '<div class="value">Connected</div>';
        echo '</div>';
        
        // Check tables
        $requiredTables = [
            'product_collections' => 'Collection data',
            'product_collection_items' => 'Product items',
            'product_files' => 'Catalog files'
        ];
        
        foreach ($requiredTables as $table => $desc) {
            $totalChecks++;
            try {
                $result = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
                $exists = !empty($result);
                
                if ($exists) {
                    $count = $pdo->query("SELECT COUNT(*) as cnt FROM $table")->fetch()['cnt'];
                    $passedChecks++;
                    
                    echo '<div class="check-item">';
                    echo '<div class="status success">✓</div>';
                    echo '<div class="label">Table: <code>' . $table . '</code></div>';
                    echo '<div class="value">' . $count . ' records</div>';
                    echo '</div>';
                } else {
                    echo '<div class="check-item">';
                    echo '<div class="status error">✗</div>';
                    echo '<div class="label">Table: <code>' . $table . '</code></div>';
                    echo '<div class="value">Not found</div>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div class="check-item">';
                echo '<div class="status error">✗</div>';
                echo '<div class="label">Table: <code>' . $table . '</code></div>';
                echo '<div class="value">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
            }
        }
        
        // Check views
        $views = ['v_collections_full', 'v_collection_items_full'];
        foreach ($views as $view) {
            $totalChecks++;
            try {
                $result = $pdo->query("SHOW FULL TABLES LIKE '$view'")->fetch();
                $exists = !empty($result);
                
                if ($exists) {
                    $passedChecks++;
                    echo '<div class="check-item">';
                    echo '<div class="status success">✓</div>';
                    echo '<div class="label">View: <code>' . $view . '</code></div>';
                    echo '<div class="value">Created</div>';
                    echo '</div>';
                } else {
                    $warnings++;
                    echo '<div class="check-item">';
                    echo '<div class="status warning">!</div>';
                    echo '<div class="label">View: <code>' . $view . '</code></div>';
                    echo '<div class="value">Not found (optional)</div>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                $warnings++;
                echo '<div class="check-item">';
                echo '<div class="status warning">!</div>';
                echo '<div class="label">View: <code>' . $view . '</code></div>';
                echo '<div class="value">Error (optional)</div>';
                echo '</div>';
            }
        }
        
    } catch (Exception $e) {
        $totalChecks++;
        echo '<div class="check-item">';
        echo '<div class="status error">✗</div>';
        echo '<div class="label">Database Connection</div>';
        echo '<div class="value">Failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '</div>';
        echo '<div class="details">';
        echo '⚠️ Cannot connect to database. Please check <code>backend/inc/db.php</code> configuration.';
        echo '</div>';
    }
    
    echo '</div>';
    
    // ===================================
    // PHP CONFIGURATION CHECKS
    // ===================================
    echo '<div class="check-section">';
    echo '<h2>⚙️ PHP Configuration</h2>';
    
    $phpChecks = [
        'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'cURL Extension' => extension_loaded('curl'),
        'PDO Extension' => extension_loaded('pdo'),
        'DOM Extension' => extension_loaded('dom'),
        'JSON Extension' => extension_loaded('json')
    ];
    
    foreach ($phpChecks as $name => $passed) {
        $totalChecks++;
        if ($passed) $passedChecks++;
        
        echo '<div class="check-item">';
        echo '<div class="status ' . ($passed ? 'success' : 'error') . '">';
        echo $passed ? '✓' : '✗';
        echo '</div>';
        echo '<div class="label">' . $name . '</div>';
        echo '<div class="value">';
        if ($name === 'PHP Version') {
            echo PHP_VERSION;
        } else {
            echo $passed ? 'Enabled' : 'Disabled';
        }
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // ===================================
    // SUMMARY
    // ===================================
    $percentage = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0;
    $status = $percentage >= 90 ? '✅ Excellent' : ($percentage >= 70 ? '⚠️ Good' : '❌ Needs Attention');
    
    echo '<div class="summary">';
    echo '<h2>' . $status . '</h2>';
    echo '<p>System Verification Complete</p>';
    echo '<div class="stats">';
    echo '<div class="stat">';
    echo '<div class="stat-value">' . $passedChecks . '/' . $totalChecks . '</div>';
    echo '<div class="stat-label">Checks Passed</div>';
    echo '</div>';
    echo '<div class="stat">';
    echo '<div class="stat-value">' . $percentage . '%</div>';
    echo '<div class="stat-label">Success Rate</div>';
    echo '</div>';
    if ($warnings > 0) {
        echo '<div class="stat">';
        echo '<div class="stat-value">' . $warnings . '</div>';
        echo '<div class="stat-label">Warnings</div>';
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';
    
    // ===================================
    // ACTIONS
    // ===================================
    echo '<div class="actions">';
    
    if ($percentage < 90) {
        echo '<a href="setup_product_collections.php" class="btn">🔧 Run Setup Script</a>';
    } else {
        echo '<a href="../fetch_product_collection.php" class="btn">🚀 Open Scraper Tool</a>';
    }
    
    echo '<a href="PRODUCT_COLLECTION_SCRAPER_DOCS.md" class="btn secondary" target="_blank">📖 Read Documentation</a>';
    echo '<a href="' . $_SERVER['PHP_SELF'] . '" class="btn secondary">🔄 Refresh Check</a>';
    echo '</div>';
    
    // ===================================
    // NEXT STEPS
    // ===================================
    if ($percentage >= 90) {
        echo '<div class="check-section" style="border-left-color: #48bb78;">';
        echo '<h2>🎉 Next Steps</h2>';
        echo '<div class="details">';
        echo '<p><strong>Your system is ready!</strong> Here\'s what you can do:</p>';
        echo '<ol style="margin-left: 20px; margin-top: 10px; line-height: 1.8;">';
        echo '<li>Open the <a href="../fetch_product_collection.php" style="color: #4299e1;">Scraper Tool</a></li>';
        echo '<li>Enter test URL: <code>https://vnbuilding.vn/vat-lieu/alhmabra-rd7ere</code></li>';
        echo '<li>Click "Crawl & Preview" to see extracted data</li>';
        echo '<li>Click "Save to Database" to import</li>';
        echo '<li>Read the <a href="README_COLLECTION_SCRAPER.md" style="color: #4299e1;">Quick Start Guide</a> for more details</li>';
        echo '</ol>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="check-section" style="border-left-color: #ed8936;">';
        echo '<h2>⚠️ Action Required</h2>';
        echo '<div class="details">';
        echo '<p><strong>Some components are missing or not configured properly.</strong></p>';
        echo '<ol style="margin-left: 20px; margin-top: 10px; line-height: 1.8;">';
        echo '<li>Run the <a href="setup_product_collections.php" style="color: #4299e1;">Setup Script</a> to create database tables</li>';
        echo '<li>Check database connection in <code>backend/inc/db.php</code></li>';
        echo '<li>Ensure all required PHP extensions are installed</li>';
        echo '<li>Refresh this page after fixing issues</li>';
        echo '</ol>';
        echo '</div>';
        echo '</div>';
    }
    ?>
    
</div>
</body>
</html>
