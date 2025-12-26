<?php
/**
 * LeadGen CMS - Database Setup Script
 * 
 * Run this script to create/reset the database tables
 * URL: http://localhost/lead_generate/setup.php
 */

// Disable for production - set to false after setup
define('ALLOW_SETUP', true);

if (!ALLOW_SETUP) {
    die('Setup is disabled. Edit setup.php to enable.');
}

// Database credentials (same as in config/database.php)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'leadgen_cms';

$messages = [];
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Use mysqli for multi_query support (handles complex SQL better)
        $mysqli = new mysqli($host, $user, $pass);
        
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }
        
        // Read and execute schema
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found: $schemaFile");
        }
        
        $sql = file_get_contents($schemaFile);
        
        // Remove comments and empty lines for cleaner execution
        $sql = preg_replace('/--.*?\n/s', "\n", $sql);
        
        // Execute multi-query
        if ($mysqli->multi_query($sql)) {
            // Process all result sets
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
            
            // Check for errors
            if ($mysqli->error) {
                throw new Exception("SQL Error: " . $mysqli->error);
            }
            
            $messages[] = "Database 'leadgen_cms' created successfully.";
            $messages[] = "All tables and views created successfully.";
            $messages[] = "Default admin user created (admin@leadgen.com / admin123)";
            $messages[] = "Default lead sources added (16 sources).";
            $messages[] = "System settings configured.";
            
        } else {
            throw new Exception("Failed to execute SQL: " . $mysqli->error);
        }
        
        $mysqli->close();
        
        // Create cache directory
        $cacheDir = __DIR__ . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
            $messages[] = "Cache directory created.";
        }
        
        // Create uploads directory
        $uploadsDir = __DIR__ . '/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
            $messages[] = "Uploads directory created.";
        }
        
        $messages[] = "<strong>Setup completed successfully!</strong>";
        $messages[] = "<a href='index.php' class='btn-primary'>Go to Application →</a>";
        
    } catch (Exception $e) {
        $errors[] = "Setup failed: " . $e->getMessage();
    }
}

// Check current status
$dbExists = false;
$tablesExist = false;

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $result = $pdo->query("SHOW DATABASES LIKE '$dbname'")->fetch();
    $dbExists = !empty($result);
    
    if ($dbExists) {
        $pdo->exec("USE `$dbname`");
        $result = $pdo->query("SHOW TABLES LIKE 'leads'")->fetch();
        $tablesExist = !empty($result);
    }
} catch (Exception $e) {
    $errors[] = "Cannot connect to MySQL: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeadGen CMS - Database Setup</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --color-primary: #6366f1;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-error: #ef4444;
            --bg-dark: #0f0f1a;
            --bg-card: rgba(26, 26, 46, 0.8);
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --border-color: rgba(255, 255, 255, 0.1);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
        }
        
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(10px);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
        }
        
        .logo-text span {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        p {
            color: var(--text-secondary);
            margin-bottom: 25px;
        }
        
        .status {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .status-item {
            flex: 1;
            padding: 15px;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            text-align: center;
        }
        
        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        
        .status-icon.success { background: rgba(16, 185, 129, 0.2); color: var(--color-success); }
        .status-icon.warning { background: rgba(245, 158, 11, 0.2); color: var(--color-warning); }
        .status-icon.error { background: rgba(239, 68, 68, 0.2); color: var(--color-error); }
        
        .status-label {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .messages {
            margin-top: 20px;
        }
        
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message.success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--color-success);
        }
        
        .message.success a {
            color: inherit;
            text-decoration: underline;
        }
        
        .message.error {
            background: rgba(239, 68, 68, 0.15);
            color: var(--color-error);
        }
        
        .warning-box {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
        }
        
        .warning-box h4 {
            color: var(--color-warning);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .warning-box p {
            font-size: 14px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="logo-text">LeadGen<span>CMS</span></div>
            </div>
            
            <h1>Database Setup</h1>
            <p>This will create the database and tables required for LeadGen CMS.</p>
            
            <div class="status">
                <div class="status-item">
                    <div class="status-icon <?php echo $dbExists ? 'success' : 'warning'; ?>">
                        <i class="fas <?php echo $dbExists ? 'fa-check' : 'fa-database'; ?>"></i>
                    </div>
                    <div class="status-label">Database</div>
                    <strong><?php echo $dbExists ? 'Exists' : 'Not Found'; ?></strong>
                </div>
                <div class="status-item">
                    <div class="status-icon <?php echo $tablesExist ? 'success' : 'warning'; ?>">
                        <i class="fas <?php echo $tablesExist ? 'fa-check' : 'fa-table'; ?>"></i>
                    </div>
                    <div class="status-label">Tables</div>
                    <strong><?php echo $tablesExist ? 'Ready' : 'Not Created'; ?></strong>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="messages">
                    <?php foreach ($errors as $error): ?>
                        <div class="message error">
                            <i class="fas fa-times-circle"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($messages)): ?>
                <div class="messages">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $msg; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <form method="POST">
                    <button type="submit" class="btn-primary" <?php echo !empty($errors) ? 'disabled' : ''; ?>>
                        <i class="fas fa-database"></i>
                        <?php echo $tablesExist ? 'Reset Database' : 'Install Database'; ?>
                    </button>
                </form>
                
                <?php if ($tablesExist): ?>
                    <div class="warning-box">
                        <h4><i class="fas fa-exclamation-triangle"></i> Warning</h4>
                        <p>Resetting will delete all existing data. Make sure to backup your data first.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
