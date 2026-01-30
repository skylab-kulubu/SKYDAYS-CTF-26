<?php
session_start();
if (!isset($_SESSION["logged_in"])) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == UPLOAD_ERR_OK) {
        $file = $_FILES["file"];
        $filename = $file["name"];
        $contents = file_get_contents($file["tmp_name"]);

        if (!is_dir('./uploads/')) {
            mkdir('./uploads/', 0755, true);
        }

        if (stripos($filename, ".php") !== false) {
            $error = "PHP files are not allowed for security reasons.";
        }
        else if (stripos($contents, "<?php") !== false) {
            $error = "File contains unsafe PHP content.";
        }
        else {
            $upload_path = "./uploads/" . $filename;
            if (move_uploaded_file($file["tmp_name"], $upload_path)) {
                $success = "File uploaded successfully! <a href='/uploads/$filename' style='color: #2d5be3;'>View uploaded file</a>";
            } else {
                $error = "File upload failed.";
            }
        }
    } else {
        $error = "Please select a file to upload.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | System Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #000000;
            color: #ffffff;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(45, 91, 227, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(45, 91, 227, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(45, 91, 227, 0.06) 0%, transparent 50%);
            animation: rotate 30s linear infinite;
            z-index: 0;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
            position: relative;
            z-index: 1;
        }
        
        header {
            background: rgba(17, 17, 17, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border: 1px solid rgba(51, 51, 51, 0.5);
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            animation: slideDown 0.6s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 300;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #ffffff 0%, #2d5be3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .user-info {
            color: #888888;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .user-info::before {
            content: '👤';
            font-size: 1.1rem;
        }
        
        .nav {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .nav a {
            color: #2d5be3;
            text-decoration: none;
            padding: 10px 20px;
            border: 1px solid rgba(45, 91, 227, 0.3);
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: rgba(45, 91, 227, 0.05);
        }
        
        .nav a:hover {
            background: #2d5be3;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 91, 227, 0.4);
        }
        
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            animation: fadeIn 0.8s ease-out 0.2s both;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .panel {
            background: rgba(17, 17, 17, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border: 1px solid rgba(51, 51, 51, 0.5);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
        }
        
        .panel:hover {
            border-color: rgba(45, 91, 227, 0.3);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
        }
        
        .panel h2 {
            color: #ffffff;
            margin-bottom: 25px;
            font-weight: 400;
            font-size: 1.4rem;
            border-bottom: 2px solid rgba(45, 91, 227, 0.3);
            padding-bottom: 12px;
            letter-spacing: 0.5px;
        }
        
        .stats {
            font-size: 1rem;
            line-height: 2;
            color: #cccccc;
        }
        
        .stats p {
            padding: 8px 0;
            border-bottom: 1px solid rgba(51, 51, 51, 0.3);
            transition: all 0.3s ease;
        }
        
        .stats p:hover {
            background: rgba(45, 91, 227, 0.05);
            padding-left: 10px;
            border-radius: 4px;
        }
        
        .stats strong {
            color: #2d5be3;
            font-weight: 500;
        }
        
        /* Upload Form Styling */
        .upload-area {
            border: 2px dashed rgba(51, 51, 51, 0.5);
            padding: 40px;
            text-align: center;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 20px;
        }
        
        .upload-area:hover {
            border-color: #2d5be3;
            background: rgba(45, 91, 227, 0.05);
        }
        
        .upload-area.dragover {
            border-color: #2d5be3;
            background: rgba(45, 91, 227, 0.1);
            transform: scale(1.02);
        }
        
        .upload-area-content {
            pointer-events: none;
        }
        
        .upload-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.6;
        }
        
        .upload-form input[type="file"] {
            display: none;
        }
        
        .file-input-label {
            display: inline-block;
            background: linear-gradient(135deg, #2d5be3 0%, #1e4fd8 100%);
            color: #ffffff;
            padding: 12px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .file-input-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 91, 227, 0.4);
        }
        
        #fileName {
            color: #2d5be3;
            margin-top: 15px;
            font-size: 0.9rem;
            min-height: 20px;
        }
        
        .upload-form input[type="submit"] {
            background: linear-gradient(135deg, #2d5be3 0%, #1e4fd8 100%);
            color: #ffffff;
            padding: 14px 40px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        
        .upload-form input[type="submit"]::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: left 0.5s;
        }
        
        .upload-form input[type="submit"]:hover::before {
            left: 100%;
        }
        
        .upload-form input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 91, 227, 0.4);
        }
        
        .upload-form input[type="submit"].loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .upload-form input[type="submit"].loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.6s linear infinite;
        }
        
        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
        
        .message {
            padding: 16px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            font-size: 0.9rem;
            animation: slideIn 0.4s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid rgba(204, 0, 0, 0.4);
            color: #ff6b6b;
        }
        
        .error::before {
            content: '❌ ';
        }
        
        .success {
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid rgba(0, 204, 0, 0.4);
            color: #90ee90;
        }
        
        .success::before {
            content: '✅ ';
        }
        
        .success a {
            color: #2d5be3;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px dashed #2d5be3;
        }
        
        .success a:hover {
            border-bottom-style: solid;
        }
        
        footer {
            text-align: center;
            margin-top: 60px;
            padding: 30px;
            color: #666666;
            border-top: 1px solid rgba(51, 51, 51, 0.3);
            font-size: 0.85rem;
            animation: fadeIn 0.8s ease-out 0.4s both;
        }
        
        .system-info {
            background: rgba(45, 91, 227, 0.1);
            padding: 18px;
            border-radius: 8px;
            margin-top: 25px;
            border: 1px solid rgba(45, 91, 227, 0.3);
            font-size: 0.85rem;
            line-height: 1.8;
        }
        
        .system-info strong {
            color: #2d5be3;
        }
        
        .system-info small {
            display: block;
            margin-top: 10px;
            color: #888;
            font-size: 0.75rem;
            padding-top: 10px;
            border-top: 1px solid rgba(45, 91, 227, 0.2);
        }
        
        .file-list {
            margin-top: 25px;
        }
        
        .file-list h3 {
            color: #cccccc;
            margin-bottom: 15px;
            font-size: 1.1rem;
            font-weight: 400;
        }
        
        .file-list ul {
            list-style: none;
            color: #888888;
        }
        
        .file-list li {
            padding: 12px;
            border-bottom: 1px solid rgba(51, 51, 51, 0.3);
            transition: all 0.3s ease;
            border-radius: 4px;
        }
        
        .file-list li:hover {
            background: rgba(45, 91, 227, 0.05);
            padding-left: 20px;
        }
        
        .file-list a {
            color: #2d5be3;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .file-list a::before {
            content: '📄';
            font-size: 1.2rem;
        }
        
        .file-list a:hover {
            color: #4d7bf3;
        }
        
        .upload-info {
            margin-top: 25px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            border: 1px solid rgba(51, 51, 51, 0.3);
        }
        
        .upload-info p {
            color: #888888;
            font-size: 0.85rem;
            line-height: 1.8;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .upload-info p::before {
            content: '���';
            color: #2d5be3;
            font-size: 1.2rem;
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .content {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            
            .panel {
                padding: 20px;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .nav {
                gap: 10px;
            }
            
            .nav a {
                font-size: 0.85rem;
                padding: 8px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">System Administration Panel</div>
            <div class="user-info">Welcome, Administrator</div>
            <div class="nav">
                <a href="/">🏠 Home</a>
                <a href="/admin.php">📊 Dashboard</a>
                <a href="/uploads/">📁 File Manager</a>
                <a href="#">⚙️ Settings</a>
                <a href="logout.php">🚪 Log Out</a>
            </div>
        </header>
        
        <div class="content">
            <div class="panel">
                <h2>📈 System Overview</h2>
                <div class="stats">
                    <?php
                        $uptime = @file_get_contents("/proc/uptime");
                        if ($uptime) {
                            $uptime = explode(" ", $uptime);
                            $uptime = $uptime[0];
                            $uptime = floor($uptime / 60);
                            echo "<p><strong>System Uptime:</strong> $uptime minutes</p>";
                        }
                        
                        $load = @sys_getloadavg();
                        if ($load) {
                            echo "<p><strong>Load Average:</strong> " . implode(", ", array_map(function($v) { return number_format($v, 2); }, $load)) . "</p>";
                        }
                        
                        echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
                        echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
                    ?>
                </div>
                
                <div class="system-info">
                    <strong>Status:</strong> All systems operational. No issues detected.
                    <small>Apache/2.4.38 | PHP <?php echo PHP_VERSION; ?> | AllowOverride: All</small>
                </div>

                <div class="file-list">
                    <h3>📂 Recently Uploaded Files</h3>
                    <?php
                    if (is_dir('./uploads/')) {
                        $files = scandir('./uploads/');
                        $hasFiles = false;
                        echo "<ul>";
                        foreach ($files as $file) {
                            if ($file != "." && $file != "..") {
                                echo "<li><a href='/uploads/$file' target='_blank'>$file</a></li>";
                                $hasFiles = true;
                            }
                        }
                        if (!$hasFiles) {
                            echo "<li style='color: #666; text-align: center; padding: 20px;'>No files uploaded yet</li>";
                        }
                        echo "</ul>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="panel">
                <h2>📤 File Management</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="message success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="admin.php" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-area-content">
                            <div class="upload-icon">📁</div>
                            <p style="color: #888; margin-bottom: 10px;">Drag and drop file here</p>
                            <p style="color: #666; font-size: 0.85rem;">or</p>
                            <label for="fileInput" class="file-input-label">Choose File</label>
                            <div id="fileName"></div>
                        </div>
                    </div>
                    <input type="file" name="file" id="fileInput" required>
                    <input type="submit" value="Upload File" id="uploadBtn">
                </form>
                
                <div class="upload-info">
                    <p>Allowed file types: Images, documents, archives</p>
                    <p>Maximum file size: 10MB</p>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2026 System Administration. All rights reserved.</p>
            <p style="margin-top: 8px;">Secure Admin Panel v4.2.1 | Last updated: <?php echo date('F Y'); ?></p>
        </footer>
    </div>

    <script>
        // File upload interactions
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');

        // Click to upload
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            displayFileName();
        });

        // File selection
        fileInput.addEventListener('change', displayFileName);

        function displayFileName() {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const size = (file.size / 1024 / 1024).toFixed(2);
                fileName.innerHTML = `<strong>Selected:</strong> ${file.name} (${size} MB)`;
            }
        }

        // Form submit loading
        uploadForm.addEventListener('submit', function() {
            uploadBtn.classList.add('loading');
            uploadBtn.value = '';
        });
    </script>
</body>
</html>
