<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR & Payroll - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --accent: #f093fb;
            --success: #4facfe;
            --danger: #f56565;
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 30%, #f093fb 70%, #f5576c 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Animated background elements */
        .bg-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(30px) rotate(240deg); }
        }

        /* Main container */
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            position: relative;
            z-index: 10;
        }

        /* Glass morphism login card */
        .login-card {
            width: 100%;
            max-width: 480px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animated border effect */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 24px;
            padding: 2px;
            background: linear-gradient(45deg, 
                rgba(255,255,255,0.5), 
                rgba(255,255,255,0.1), 
                rgba(255,255,255,0.5));
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: xor;
            -webkit-mask-composite: xor;
            animation: borderRotate 4s linear infinite;
        }

        @keyframes borderRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Header styling */
        .login-header {
            background: linear-gradient(135deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .login-title {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            font-size: 1rem;
            position: relative;
            z-index: 2;
        }

        .login-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            animation: iconPulse 3s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Form body */
        .login-body {
            padding: 2.5rem 2rem;
            position: relative;
            z-index: 2;
        }

        /* Modern form controls */
        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control-modern {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            padding: 1rem 1.2rem;
            font-size: 1rem;
            font-weight: 500;
            color: #2d3748;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .form-control-modern:focus {
            border-color: rgba(102, 126, 234, 0.8);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15),
                        0 10px 25px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-2px);
        }

        .form-control-modern::placeholder {
            color: rgba(45, 55, 72, 0.6);
            transition: all 0.3s ease;
        }

        .form-control-modern:focus::placeholder {
            opacity: 0;
            transform: translateY(-10px);
        }

        /* Floating labels */
        .form-label-floating {
            position: absolute;
            top: 50%;
            left: 1.2rem;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 0 0.5rem;
            color: rgba(45, 55, 72, 0.7);
            font-weight: 500;
            font-size: 1rem;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
        }

        .form-control-modern:focus + .form-label-floating,
        .form-control-modern:not(:placeholder-shown) + .form-label-floating {
            top: 0;
            transform: translateY(-50%) scale(0.85);
            color: var(--primary);
            font-weight: 600;
        }

        /* Password toggle button */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(45, 55, 72, 0.6);
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 3;
        }

        .password-toggle:hover {
            color: var(--primary);
            transform: translateY(-50%) scale(1.1);
        }

        /* Modern checkbox */
        .form-check-modern {
            position: relative;
            margin: 1.5rem 0;
        }

        .form-check-input-modern {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-check-input-modern:checked {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.2);
        }

        .form-check-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            margin-left: 0.5rem;
            cursor: pointer;
        }

        /* Gradient login button */
        .btn-login-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 1rem 2rem;
            border-radius: 16px;
            width: 100%;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-login-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s ease;
        }

        .btn-login-modern:hover::before {
            left: 100%;
        }

        .btn-login-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .btn-login-modern:active {
            transform: translateY(-1px);
        }

        /* Forgot password link */
        .forgot-link {
            display: inline-block;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
        }

        .forgot-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .forgot-link:hover::before {
            width: 100%;
        }

        .forgot-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        /* Alert styling */
        .alert-modern {
            border: none;
            border-radius: 16px;
            backdrop-filter: blur(10px);
            font-weight: 500;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .alert-success-modern {
            background: rgba(72, 187, 120, 0.2);
            color: #2f855a;
            border-left: 4px solid #48bb78;
        }

        .alert-danger-modern {
            background: rgba(245, 101, 101, 0.2);
            color: #c53030;
            border-left: 4px solid #f56565;
        }

        /* Loading spinner */
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Navbar (simplified for login) */
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 800;
            font-size: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .navbar-brand i {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-right: 0.5rem;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(20px);
            color: rgba(255, 255, 255, 0.8);
            padding: 1.5rem 0;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Input focus glow effect */
        .input-glow {
            position: relative;
        }

        .input-glow::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
            filter: blur(8px);
        }

        .form-control-modern:focus + .input-glow::after {
            opacity: 0.3;
        }

        /* Responsive design */
        @media (max-width: 576px) {
            .login-container {
                padding: 1rem;
            }
            
            .login-body {
                padding: 2rem 1.5rem;
            }
            
            .login-header {
                padding: 2rem 1.5rem;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
        }

        /* Success state animation */
        @keyframes successPulse {
            0% { box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(72, 187, 120, 0); }
            100% { box-shadow: 0 0 0 0 rgba(72, 187, 120, 0); }
        }

        .success-state {
            animation: successPulse 2s infinite;
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .form-control-modern {
                background: rgba(26, 32, 44, 0.8);
                color: white;
            }
            
            .form-control-modern::placeholder {
                color: rgba(255, 255, 255, 0.6);
            }
        }
    </style>
</head>
<body>
    <!-- Animated background elements -->
    <div class="bg-elements">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-user-clock"></i>HR & Payroll
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to access your dashboard</p>
            </div>
            
            <div class="login-body">
                <!-- Success/Error Alerts -->
                @if (session('success'))
                <div class="alert alert-success-modern alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if (session('error'))
                <div class="alert alert-danger-modern alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Laravel Form with CSRF -->
                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    
                    <!-- Email Field -->
                    <div class="form-floating">
                        <div class="input-glow"></div>
                        <input type="email" class="form-control form-control-modern @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" 
                               required autocomplete="email" autofocus>
                        <label for="email" class="form-label-floating">
                            <i class="fas fa-envelope me-2"></i>Email Address
                        </label>
                        @error('email')
                        <div class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-floating position-relative">
                        <div class="input-glow"></div>
                        <input type="password" class="form-control form-control-modern @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Enter your password" 
                               required autocomplete="current-password">
                        <label for="password" class="form-label-floating">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                        </button>
                        @error('password')
                        <div class="invalid-feedback d-block">
                            <strong>{{ $message }}</strong>
                        </div>
                        @enderror
                    </div>
                    
                    <!-- Remember Me -->
                    <div class="form-check form-check-modern d-flex align-items-center">
                        <input class="form-check-input form-check-input-modern" type="checkbox" 
                               name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" class="btn btn-login-modern" id="loginBtn">
                        <span class="loading-spinner" id="loadingSpinner"></span>
                        <span id="loginText">
                            <i class="fas fa-sign-in-alt me-2"></i>{{ __('Login') }}
                        </span>
                    </button>
                    
                    <!-- Forgot Password -->
                    @if (Route::has('password.request'))
                    <div class="text-center mt-4">
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            <i class="fas fa-key me-2"></i>{{ __('Forgot Your Password?') }}
                        </a>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="mb-0">© 2025 Zamfara HR & Payroll. All rights reserved. | Powered by Innovation</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Page initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus animations to form controls
            const formControls = document.querySelectorAll('.form-control-modern');
            formControls.forEach(control => {
                control.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                control.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });

            // Add ripple effect to button
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.addEventListener('click', function(e) {
                let ripple = document.createElement('span');
                let rect = this.getBoundingClientRect();
                let size = Math.max(rect.width, rect.height);
                let x = e.clientX - rect.left - size / 2;
                let y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.3);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        });

        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form submission (Laravel integration)
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const loginText = document.getElementById('loginText');
            
            // Show loading state
            loginBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            loginText.innerHTML = 'Signing In...';
            
            // Let the form submit normally to Laravel
            // The loading state will be visible until page redirect
        });

        // Real-time form validation
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        emailInput.addEventListener('blur', function() {
            const feedback = this.parentElement.querySelector('.invalid-feedback');
            if (!validateEmail(this.value)) {
                this.classList.add('is-invalid');
                feedback.textContent = 'Please enter a valid email address';
                feedback.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                feedback.style.display = 'none';
            }
        });

        passwordInput.addEventListener('blur', function() {
            const feedback = this.parentElement.querySelector('.invalid-feedback');
            if (this.value.length < 6) {
                this.classList.add('is-invalid');
                feedback.textContent = 'Password must be at least 6 characters long';
                feedback.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                feedback.style.display = 'none';
            }
        });

        // Parallax scroll effect for background
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            document.querySelector('.bg-elements').style.transform = `translateY(${rate}px)`;
        });

        // Add enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && document.activeElement.tagName !== 'BUTTON') {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });

        // Auto-focus first input
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('email').focus();
            }, 500);
        });

        // Add focus styling to parent elements
        document.querySelectorAll('.form-control-modern').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.form-floating').classList.add('input-focused');
            });
            
            input.addEventListener('blur', function() {
                this.closest('.form-floating').classList.remove('input-focused');
            });
        });

        // Add CSS for focus styling
        const focusStyle = document.createElement('style');
        focusStyle.textContent = `
            .input-focused {
                transform: scale(1.02);
            }
            
            .form-control-modern.is-valid {
                border-color: rgba(72, 187, 120, 0.8);
                box-shadow: 0 0 0 0.2rem rgba(72, 187, 120, 0.15);
            }
            
            .form-control-modern.is-invalid {
                border-color: rgba(245, 101, 101, 0.8);
                box-shadow: 0 0 0 0.2rem rgba(245, 101, 101, 0.15);
            }
        `;
        document.head.appendChild(focusStyle);
    </script>
</body>
</html>