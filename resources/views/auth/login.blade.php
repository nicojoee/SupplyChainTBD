<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Supply Chain Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Calm & Elegant Palette */
            --bg-page: #f8fafc;
            --bg-glass: rgba(255, 255, 255, 0.85);
            --primary: #3b82f6;
            --primary-soft: #eff6ff;
            --text-dark: #0f172a;
            --text-medium: #475569;
            --text-light: #94a3b8;
            --accent-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Split Layout */
        .container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        /* Left Side - Visual & Features */
        .visual-side {
            flex: 1.2;
            background: #0f172a;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: white;
        }

        /* Calm Abstract Background */
        .visual-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(99, 102, 241, 0.15) 0%, transparent 40%);
            z-index: 0;
        }

        .visual-content {
            position: relative;
            z-index: 10;
            max-width: 500px;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 2rem;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Features List */
        .features-list {
            list-style: none;
            display: grid;
            gap: 2rem;
        }

        .feature-item {
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
            opacity: 0;
            animation: fadeInSlide 0.8s forwards;
        }
        
        .feature-item:nth-child(1) { animation-delay: 0.2s; }
        .feature-item:nth-child(2) { animation-delay: 0.4s; }
        .feature-item:nth-child(3) { animation-delay: 0.6s; }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .feature-text h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .feature-text p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.5;
        }

        /* Right Side - Login Form */
        .login-side {
            flex: 1;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            box-shadow: -20px 0 40px rgba(0,0,0,0.02);
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .brand-logo {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .welcome-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            font-size: 0.95rem;
            color: var(--text-medium);
            margin-bottom: 2.5rem;
        }

        /* Google Button Style */
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 1rem;
            background-color: white;
            color: var(--text-medium);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .google-btn:hover {
            border-color: #cbd5e1;
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            color: var(--text-dark);
        }

        .google-btn:active {
            transform: translateY(0);
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #ef4444;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Footer Contacts - Clean & Modern */
        .footer-contacts {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #f1f5f9;
        }

        .footer-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 1rem;
            text-align: center;
        }

        .contact-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .contact-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            color: var(--text-medium);
            text-decoration: none;
            transition: background 0.2s;
        }

        .contact-item:hover {
            background: #f8fafc;
            color: var(--primary);
        }

        .nrp {
            font-size: 0.75rem;
            color: var(--text-light);
            font-family: monospace;
        }

        @keyframes fadeInSlide {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 960px) {
            .visual-side { display: none; }
            .login-side { flex: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Visualization Side -->
        <div class="visual-side">
            <div class="visual-bg"></div>
            <div class="visual-content">
                <h1 class="hero-title">Intelligent Supply Chain Management</h1>
                
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">🌏</div>
                        <div class="feature-text">
                            <h3>Real-time GIS Tracking</h3>
                            <p>Monitor your fleet and shipments globally with precision GPS tracking and route optimization.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">🔗</div>
                        <div class="feature-text">
                            <h3>End-to-End Visibility</h3>
                            <p>Seamless connection from suppliers to distributors. Track every step of your product's journey.</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">🛡️</div>
                        <div class="feature-text">
                            <h3>Secure Collaboration</h3>
                            <p>Verify identities and manage access with enterprise-grade security for all stakeholders.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Side -->
        <div class="login-side">
            <div class="login-wrapper">
                <div class="brand-logo">🌐</div>
                <h2 class="welcome-title">Welcome Back</h2>
                <p class="welcome-subtitle">Seamlessly manage your logistics network.</p>

                @if(session('error'))
                    <div class="alert-error">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <a href="{{ route('auth.google') }}" class="google-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with ITS Google Account
                </a>

                <div style="margin-top: 1.5rem; text-align: center; font-size: 0.8rem; color: #94a3b8;">
                    By creating an account, you agree to our Terms of Service and Privacy Policy.
                </div>

                <!-- Contact Footer -->
                <div class="footer-contacts">
                    <div class="footer-title">Need Access? Contact Superadmin</div>
                    <div class="contact-list">
                        <a href="mailto:5002221003@student.its.ac.id" class="contact-item">
                            <span>Nicholas Joe Sumantri</span>
                            <span class="nrp">5002221003</span>
                        </a>
                        <a href="mailto:50002221041@student.its.ac.id" class="contact-item">
                            <span>Nabilah Safa Nur Fatimah</span>
                            <span class="nrp">50002221041</span>
                        </a>
                        <a href="mailto:5002221055@student.its.ac.id" class="contact-item">
                            <span>Marsyanda Auditya</span>
                            <span class="nrp">5002221055</span>
                        </a>
                        <a href="mailto:5002221084@student.its.ac.id" class="contact-item">
                            <span>Moch Fajar Aditya Putra</span>
                            <span class="nrp">5002221084</span>
                        </a>
                        <a href="mailto:5002221085@student.its.ac.id" class="contact-item">
                            <span>Prasasti Intan Pratiwi</span>
                            <span class="nrp">5002221085</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Clear dismissed broadcasts when user visits login page (after logout)
        localStorage.removeItem('dismissedBroadcasts');
    </script>
</body>
</html>
