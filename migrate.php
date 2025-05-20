<?php
// migrate.php - A simple database migration runner
require_once 'db.php';

// Configuration
$migrationsDir = __DIR__ . '/migrations/';
$command = $argv[1] ?? 'help';

// Colors for console output
function colorText($text, $color) {
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m"
    ];
    return $colors[$color] . $text . $colors['reset'];
}

// Get the current batch number
function getCurrentBatch($pdo) {
    $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) AS current_batch FROM migrations");
    return (int)$stmt->fetchColumn();
}

// Get applied migrations
function getAppliedMigrations($pdo) {
    $stmt = $pdo->query("SELECT migration_name FROM migrations ORDER BY id");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Run new migrations
function runMigrations($pdo, $migrationsDir) {
    try {
        // Get all SQL migration files
        $migrationFiles = glob($migrationsDir . '*.sql');
        natsort($migrationFiles);
        
        // Special handling for the first migration (create migrations table)
        if (count($migrationFiles) > 0) {
            $firstMigration = $migrationFiles[0];
            $firstMigrationName = basename($firstMigration);
            
            // Check if migrations table exists
            $tableExists = false;
            try {
                $pdo->query("SELECT 1 FROM migrations LIMIT 1");
                $tableExists = true;
            } catch (PDOException $e) {
                // Table doesn't exist, which is expected
                $tableExists = false;
            }
            
            if (!$tableExists && $firstMigrationName === '001_create_migrations_table.sql') {
                echo "Running initial migration to create migrations table...\n";
                $sql = file_get_contents($firstMigration);
                $pdo->exec($sql);
                
                echo colorText("Initial migration completed successfully!", 'green') . "\n";
                
                // Remove first migration from the list so it's not processed again
                array_shift($migrationFiles);
            }
        }
        
        // Ensure migrations table exists for subsequent migrations
        createMigrationsTable($pdo);
        
        // Get applied migrations
        $appliedMigrations = getAppliedMigrations($pdo);
        $batch = getCurrentBatch($pdo) + 1;
        
        $migrationsRun = 0;
        
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file);
            
            // Skip if already applied
            if (in_array($migrationName, $appliedMigrations)) {
                echo "Skipping " . colorText($migrationName, 'blue') . " (already applied)\n";
                continue;
            }
            
            echo "Running migration: " . colorText($migrationName, 'yellow') . "... ";
            
            // Read migration file
            $sql = file_get_contents($file);
            
            // Execute directly without transaction for regular SQL migrations
            try {
                // Execute migration
                $pdo->exec($sql);
                
                // Record the migration
                $stmt = $pdo->prepare("INSERT INTO migrations (migration_name, batch) VALUES (?, ?)");
                $stmt->execute([$migrationName, $batch]);
                
                echo colorText("OK", 'green') . "\n";
                $migrationsRun++;
            } catch (Exception $e) {
                echo colorText("FAILED", 'red') . "\n";
                echo colorText("Error: " . $e->getMessage(), 'red') . "\n";
                break;
            }
        }
        
        if ($migrationsRun > 0) {
            echo "\n" . colorText("$migrationsRun migrations completed successfully.", 'green') . "\n";
        } else {
            echo "\n" . colorText("No new migrations to run.", 'blue') . "\n";
        }
        
    } catch (Exception $e) {
        echo colorText("Migration error: " . $e->getMessage(), 'red') . "\n";
    }
}

// Rollback the last batch of migrations
function rollbackLastBatch($pdo) {
    try {
        // Get the last batch number
        $stmt = $pdo->query("SELECT MAX(batch) AS last_batch FROM migrations");
        $lastBatch = $stmt->fetchColumn();
        
        if (!$lastBatch) {
            echo colorText("No migrations to roll back.", 'yellow') . "\n";
            return;
        }
        
        // Get migrations from the last batch
        $stmt = $pdo->prepare("SELECT migration_name FROM migrations WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($migrations) === 0) {
            echo colorText("No migrations to roll back.", 'yellow') . "\n";
            return;
        }
        
        echo "Rolling back batch $lastBatch...\n";
        $migrationsRolledBack = 0;
        
        foreach ($migrations as $migration) {
            echo "Rolling back: " . colorText($migration, 'yellow') . "... ";
            
            // For this simple implementation, we don't actually run downgrade SQL
            // Instead, we just remove the migration record
            
            try {
                $stmt = $pdo->prepare("DELETE FROM migrations WHERE migration_name = ?");
                $stmt->execute([$migration]);
                
                echo colorText("OK", 'green') . "\n";
                $migrationsRolledBack++;
            } catch (Exception $e) {
                echo colorText("FAILED", 'red') . "\n";
                echo colorText("Error: " . $e->getMessage(), 'red') . "\n";
                break;
            }
        }
        
        if ($migrationsRolledBack > 0) {
            echo "\n" . colorText("$migrationsRolledBack migrations rolled back successfully.", 'green') . "\n";
            echo colorText("Note: This only removed migration records. To revert database changes, you need to manually run down SQL files.", 'yellow') . "\n";
        }
        
    } catch (Exception $e) {
        echo colorText("Rollback error: " . $e->getMessage(), 'red') . "\n";
    }
}

// Create a new migration file
function createMigration($pdo, $migrationsDir, $name) {
    $timestamp = date('YmdHis');
    $filename = $timestamp . '_' . strtolower(str_replace(' ', '_', $name)) . '.sql';
    $path = $migrationsDir . $filename;
    
    $template = "-- Migration: $filename\n-- Description: " . ucfirst($name) . "\n\n-- Write your SQL statements here\n";
    
    if (file_put_contents($path, $template)) {
        echo colorText("Created migration: $filename", 'green') . "\n";
    } else {
        echo colorText("Failed to create migration file.", 'red') . "\n";
    }
}

// Create migrations table if it doesn't exist
function createMigrationsTable($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}

// Show help information
function showHelp() {
    echo "Database Migration Tool\n\n";
    echo "Usage:\n";
    echo "  php migrate.php [command] [options]\n\n";
    echo "Available commands:\n";
    echo "  help               Show this help information\n";
    echo "  migrate            Run all pending migrations\n";
    echo "  rollback           Rollback the last batch of migrations\n";
    echo "  create [name]      Create a new migration file with the given name\n";
    echo "  status             Show migration status\n";
}

// Show migration status
function showStatus($pdo, $migrationsDir) {
    try {
        // Ensure migrations table exists
        createMigrationsTable($pdo);
        
        // Get applied migrations
        $stmt = $pdo->query("
            SELECT migration_name, batch, applied_at 
            FROM migrations 
            ORDER BY id
        ");
        $appliedMigrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all migration files
        $migrationFiles = glob($migrationsDir . '*.sql');
        natsort($migrationFiles);
        
        $appliedNames = array_column($appliedMigrations, 'migration_name');
        
        echo "Migration Status:\n\n";
        
        // Table header
        echo str_pad("Migration", 50) . " | " . str_pad("Batch", 10) . " | " . "Status\n";
        echo str_repeat("-", 80) . "\n";
        
        // List applied migrations
        foreach ($appliedMigrations as $migration) {
            echo str_pad($migration['migration_name'], 50) . " | ";
            echo str_pad($migration['batch'], 10) . " | ";
            echo colorText("Applied", 'green') . " (" . $migration['applied_at'] . ")\n";
        }
        
        // List pending migrations
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file);
            
            if (!in_array($migrationName, $appliedNames)) {
                echo str_pad($migrationName, 50) . " | ";
                echo str_pad("-", 10) . " | ";
                echo colorText("Pending", 'yellow') . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo colorText("Status error: " . $e->getMessage(), 'red') . "\n";
    }
}

// Main command processing
switch ($command) {
    case 'migrate':
        runMigrations($pdo, $migrationsDir);
        break;
        
    case 'rollback':
        rollbackLastBatch($pdo);
        break;
        
    case 'create':
        $name = $argv[2] ?? 'unnamed_migration';
        createMigration($pdo, $migrationsDir, $name);
        break;
        
    case 'status':
        showStatus($pdo, $migrationsDir);
        break;
        
    case 'help':
    default:
        showHelp();
        break;
} 