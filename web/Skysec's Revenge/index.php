<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login | System Access</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
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
                radial-gradient(circle at 20% 50%, rgba(45, 91, 227, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(45, 91, 227, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(45, 91, 227, 0.08) 0%, transparent 50%);
            animation: rotate 25s linear infinite;
            z-index: 0;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Floating particles */
        .particle {
            position: fixed;
            width: 3px;
            height: 3px;
            background: rgba(45, 91, 227, 0.3);
            border-radius: 50%;
            pointer-events: none;
            animation: float 20s linear infinite;
            z-index: 0;
        }
        
        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }
        
        .login-container {
            background: rgba(17, 17, 17, 0.95);
            backdrop-filter: blur(10px);
            padding: 50px;
            border: 1px solid rgba(51, 51, 51, 0.5);
            border-radius: 12px;
            width: 100%;
            max-width: 420px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.6),
                0 0 80px rgba(45, 91, 227, 0.1);
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Glow effect on hover */
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 12px;
            padding: 2px;
            background: linear-gradient(45deg, 
                transparent,
                rgba(45, 91, 227, 0.3),
                transparent
            );
            -webkit-mask: 
                linear-gradient(#fff 0 0) content-box, 
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .login-container:hover::before {
            opacity: 1;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeIn 0.8s ease-out 0.2s both;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo h1 {
            font-size: 2.4rem;
            color: #ffffff;
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #ffffff 0%, #2d5be3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo p {
            color: #888888;
            font-size: 0.95rem;
            font-weight: 300;
            letter-spacing: 0.5px;
        }
        
        /* Security badge */
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(45, 91, 227, 0.1);
            border: 1px solid rgba(45, 91, 227, 0.3);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            color: #2d5be3;
            margin-top: 10px;
        }
        
        .security-badge::before {
            content: '🔒';
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            animation: fadeIn 0.8s ease-out 0.4s both;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #cccccc;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333333;
            border-radius: 6px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #2d5be3;
            background: rgba(0, 0, 0, 0.8);
            box-shadow: 0 0 0 3px rgba(45, 91, 227, 0.1);
            transform: translateY(-2px);
        }
        
        .form-control::placeholder {
            color: #555555;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #2d5be3 0%, #1e4fd8 100%);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out 0.6s both;
        }
        
        .btn-login::before {
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
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 91, 227, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .warning {
            background: rgba(255, 0, 0, 0.08);
            border: 1px solid rgba(204, 0, 0, 0.3);
            color: #ff6b6b;
            padding: 14px;
            border-radius: 6px;
            margin-top: 25px;
            text-align: center;
            font-size: 0.85rem;
            line-height: 1.5;
            animation: fadeIn 0.8s ease-out 0.8s both;
        }
        
        .warning::before {
            content: '⚠️';
            margin-right: 8px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #222222;
            color: #666666;
            font-size: 0.8rem;
            animation: fadeIn 0.8s ease-out 1s both;
        }
        
        .language-select {
            text-align: center;
            margin-bottom: 25px;
            animation: fadeIn 0.8s ease-out 0.3s both;
        }
        
        .language-select select {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333333;
            color: #ffffff;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .language-select select:hover {
            border-color: #2d5be3;
            background: rgba(0, 0, 0, 0.8);
        }
        
        .language-select select:focus {
            outline: none;
            border-color: #2d5be3;
            box-shadow: 0 0 0 3px rgba(45, 91, 227, 0.1);
        }
        
        /* Loading state for button */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-login.loading::after {
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
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 35px 25px;
                margin: 20px;
            }
            
            .logo h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating particles -->
    <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
    <div class="particle" style="left: 20%; animation-delay: 2s;"></div>
    <div class="particle" style="left: 30%; animation-delay: 4s;"></div>
    <div class="particle" style="left: 40%; animation-delay: 1s;"></div>
    <div class="particle" style="left: 50%; animation-delay: 3s;"></div>
    <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
    <div class="particle" style="left: 70%; animation-delay: 2.5s;"></div>
    <div class="particle" style="left: 80%; animation-delay: 4.5s;"></div>
    <div class="particle" style="left: 90%; animation-delay: 1.5s;"></div>

    <div class="login-container">
        <div class="language-select">
            <select>
                <option>🇬🇧 English</option>
                <option>🇪🇸 Español</option>
                <option>🇫🇷 Français</option>
                <option>🇩🇪 Deutsch</option>
            </select>
        </div>

        <div class="logo">
            <h1>System Access</h1>
            <p>Secure Authentication Portal</p>
            <div class="security-badge">SSL Encrypted</div>
        </div>
        
        <form method="POST" action="login.php" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn-login" id="loginBtn">Sign In</button>
        </form>
        
        <div class="warning">
            For security reasons, please do not use passwords from other services.
        </div>
        
        <div class="footer">
            <p>&copy; 2026 System Administration. All rights reserved.</p>
            <p style="margin-top: 8px;">Version 4.2.1 | Secure Login Portal</p>
        </div>
    </div>

    <script>
        // Loading animation on form submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.textContent = '';
        });

        // Add focus effects
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
