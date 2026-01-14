<?php
/**
 * Quick Setup Script for Product Collection System
 * Run this file to create all necessary tables
 */

require __DIR__ . '/../inc/db.php';

header('Content-Type: text/html; charset=utf-8');
echo '<html><head><meta charset="utf-8"><style>
body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
.success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
h1 { color: #333; }
</style></head><body>';

echo '<h1>🚀 Product Collection System - Setup</h1>';

try {
    $pdo = getPDO();
    
    // Read migration SQL file
    $migrationFile = __DIR__ . '/migration_product_collections.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    echo '<div class="info">📄 Reading migration file: ' . basename($migrationFile) . '</div>';
    
    // Split SQL statements (simple approach)
    $statements = array_filter(
        array_map('trim', preg_split('/;[\r\n]+/', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   strpos($stmt, '--') !== 0 && 
                   strpos($stmt, '/*') !== 0;
        }
    );
    
    echo '<div class="info">📊 Found ' . count($statements) . ' SQL statements to execute</div>';
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        try {
            // Skip comments and empty lines
            $statement = trim($statement);
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }
            
            $pdo->exec($statement);
            $success++;
            
            // Show what was created
            if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $statement, $matches)) {
                echo '<div class="success">✅ Created table: ' . $matches[1] . '</div>';
            } elseif (preg_match('/CREATE.*?VIEW.*?`(\w+)`/i', $statement, $matches)) {
                echo '<div class="success">✅ Created view: ' . $matches[1] . '</div>';
            } elseif (preg_match('/ALTER TABLE.*?`(\w+)`/i', $statement, $matches)) {
                echo '<div class="success">✅ Altered table: ' . $matches[1] . '</div>';
            } elseif (preg_match('/INSERT INTO.*?`(\w+)`/i', $statement, $matches)) {
                echo '<div class="success">✅ Inserted sample data into: ' . $matches[1] . '</div>';
            }
            
        } catch (PDOException $e) {
            // Ignore "already exists" errors
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate') !== false) {
                echo '<div class="info">ℹ️ Skipped (already exists): ' . substr($statement, 0, 50) . '...</div>';
            } else {
                $errors++;
                echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
            }
        }
    }
    
    echo '<hr>';
    echo '<div class="success"><h2>✅ Setup Completed!</h2>';
    echo '<p><strong>Success:</strong> ' . $success . ' operations</p>';
    echo '<p><strong>Errors:</strong> ' . $errors . ' errors</p>';
    echo '</div>';
    
    // Verify tables were created
    echo '<h2>📊 Verification - Created Tables</h2>';
    
    $tables = ['product_collections', 'product_collection_items', 'product_files'];
    
    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
            if ($result) {
                $count = $pdo->query("SELECT COUNT(*) as cnt FROM $table")->fetch()['cnt'];
                echo '<div class="success">✅ Table <strong>' . $table . '</strong> exists (rows: ' . $count . ')</div>';
                
                // Show structure
                echo '<details><summary>View structure</summary>';
                echo '<pre>';
                $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($columns as $col) {
                    echo $col['Field'] . ' | ' . $col['Type'] . ' | ' . $col['Null'] . ' | ' . $col['Key'] . "\n";
                }
                echo '</pre></details>';
            } else {
                echo '<div class="error">❌ Table <strong>' . $table . '</strong> NOT found</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ Error checking table ' . $table . ': ' . $e->getMessage() . '</div>';
        }
    }
    
    // Check views
    echo '<h2>👁️ Verification - Created Views</h2>';
    $views = ['v_collections_full', 'v_collection_items_full'];
    
    foreach ($views as $view) {
        try {
            $result = $pdo->query("SHOW FULL TABLES LIKE '$view'")->fetch();
            if ($result) {
                echo '<div class="success">✅ View <strong>' . $view . '</strong> exists</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ View <strong>' . $view . '</strong> NOT found</div>';
        }
    }
    
    echo '<hr>';
    echo '<h2>🎯 Next Steps</h2>';
    echo '<div class="info">';
    echo '<ol>';
    echo '<li>Go to <a href="../fetch_product_collection.php">Product Collection Scraper</a></li>';
    echo '<li>Enter URL: <code>https://vnbuilding.vn/vat-lieu/alhmabra-rd7ere</code></li>';
    echo '<li>Click "Crawl & Preview" to test</li>';
    echo '<li>Click "Save to Database" to import</li>';
    echo '</ol>';
    echo '<p><strong>Documentation:</strong> <a href="./PRODUCT_COLLECTION_SCRAPER_DOCS.md">Read Docs</a></p>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="error"><h2>❌ Fatal Error</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
    echo '</div>';
}

echo '</body></html>';
?>
