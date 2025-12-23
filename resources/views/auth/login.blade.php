<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Supply Chain Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #f8fafc;
            --primary: #3b82f6;
            --text-dark: #0f172a;
            --text-medium: #475569;
            --text-light: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            /* Using Outfit for headings and key text, Plus Jakarta for body */
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
        }

        /* Split Layout */
        .container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Visual Side (Left/Top) */
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

        .visual-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: 
                radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.2) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(99, 102, 241, 0.2) 0%, transparent 40%);
            z-index: 0;
            opacity: 0.8;
        }

        .visual-content {
            position: relative;
            z-index: 10;
            max-width: 500px;
        }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.03em;
        }

        .features-list {
            display: grid;
            gap: 2rem;
        }

        .feature-item {
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .feature-icon {
            width: 52px; height: 52px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }
        
        .feature-item:hover .feature-icon {
            transform: scale(1.05) rotate(5deg);
            background: rgba(255, 255, 255, 0.1);
        }

        .feature-text h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            letter-spacing: -0.01em;
        }
        .feature-text p {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
            font-weight: 400;
        }

        /* Login Side (Right/Bottom) */
        .login-side {
            flex: 1;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow-y: auto;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 2rem 0;
        }

        .brand-logo { font-size: 3rem; margin-bottom: 0.75rem; display: inline-block; animation: float 6s ease-in-out infinite; }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }

        .welcome-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.02em;
        }
        .welcome-subtitle {
            font-size: 1rem;
            color: var(--text-medium);
            margin-bottom: 2.5rem;
            font-weight: 400;
            letter-spacing: 0.01em;
        }

        .google-btn {
            display: flex; align-items: center; justify-content: center; gap: 14px;
            width: 100%; padding: 1.1rem;
            background-color: white; color: var(--text-medium);
            border: 1px solid #e2e8f0; border-radius: 16px;
            font-size: 1rem; font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer; text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        }
        .google-btn:hover {
            border-color: #cbd5e1; background-color: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            color: var(--text-dark);
        }

        .alert-error {
            background-color: #FEF2F2; border: 1px solid #FECACA; color: #DC2626;
            padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;
            font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem;
            font-weight: 500;
        }

        /* Footer Contacts - Creative Design */
        .footer-contacts {
            margin-top: 2.5rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
        }
        .footer-title {
            font-family: 'Outfit', sans-serif;
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em;
            color: var(--text-light); font-weight: 700; margin-bottom: 1rem;
            text-align: center;
        }
        .contact-list {
            display: flex; flex-direction: column; gap: 0.5rem;
            font-size: 0.85rem;
        }
        .contact-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.6rem 0.8rem; border-radius: 10px;
            color: var(--text-medium); text-decoration: none;
            transition: all 0.2s;
            background: white;
            border: 1px solid transparent;
        }
        .contact-item:hover {
            border-color: #e2e8f0;
            background: white;
            transform: translateX(4px);
            color: var(--primary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .nrp {
            font-size: 0.75rem;
            color: var(--text-light);
            font-family: 'Outfit', monospace;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Dept Footer - Styled */
        .dept-footer {
            margin-top: 2rem;
            text-align: center;
            color: var(--text-light);
            font-size: 0.8rem;
            line-height: 1.6;
            font-family: 'Outfit', sans-serif;
            position: relative;
        }
        
        .dept-name {
            font-weight: 600;
            color: var(--text-medium);
            letter-spacing: -0.01em;
        }
        
        .dept-group {
            font-weight: 500;
            color: var(--primary);
        }

        /* Mobile Responsive */
        @media (max-width: 960px) {
            .container { flex-direction: column; }
            
            .visual-side {
                padding: 4rem 2rem;
                flex: none;
                min-height: 40vh;
            }

            .hero-title { font-size: 2.5rem; }
            .feature-text p { display: none; }
            .feature-item { align-items: center; }
            .features-list { gap: 1rem; margin-top: 1.5rem; }
            .feature-icon { width: 42px; height: 42px; font-size: 1.25rem; }
            .feature-text h3 { font-size: 1rem; margin: 0; }
            
            .login-side { padding: 2rem; flex: 1; border-radius: 24px 24px 0 0; margin-top: -24px; z-index: 20; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Key Features Visual Side -->
        <div class="visual-side">
            <div class="visual-bg"></div>
            <div class="visual-content">
                <h1 class="hero-title">Supply Chain <br>Intelligence</h1>
                
                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon">🌐</div>
                        <div class="feature-text">
                            <h3>Real-time Tracking</h3>
                            <p>Monitor your fleet and shipments globally with precision GPS.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">🏭</div>
                        <div class="feature-text">
                            <h3>Integrated Ecosystem</h3>
                            <p>Unified workflow for Suppliers, Factories & Distributors.</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">🔐</div>
                        <div class="feature-text">
                            <h3>Secure Access</h3>
                            <p>Enterprise-grade security and role-based verification.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Side -->
        <div class="login-side">
            <div class="login-wrapper">
                <div style="text-align: center;">
                    <div class="brand-logo">📦</div>
                    <h2 class="welcome-title">Sign In</h2>
                    <p class="welcome-subtitle">Seamlessly manage your logistics network.</p>
                </div>

                @if(session('error'))
                    <div class="alert-error">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <a href="{{ route('auth.google') }}" class="google-btn">
                    <svg width="22" height="22" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with Google Account
                </a>

                <div style="margin-top: 1.5rem; text-align: center; font-size: 0.8rem; color: #94a3b8; font-weight: 500;">
                    Use your institutional account to continue.
                </div>

                <!-- Superadmin Contacts -->
                <div class="footer-contacts">
                    <div class="footer-title">Need Access? Contact Superadmin</div>
                    <div class="contact-list">
                        <a href="mailto:5002221003@student.its.ac.id" class="contact-item">
                            <span>Nicholas Joe Sumantri</span>
                            <span class="nrp">5002221003</span>
                        </a>
                        <a href="mailto:5002221041@student.its.ac.id" class="contact-item">
                            <span>Nabilah Safa Nur Fatimah</span>
                            <span class="nrp">5002221041</span>
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

                <!-- Academic Footer -->
                <div class="dept-footer">
                    <div class="dept-name">Department of Mathematics ITS</div>
                    <div class="dept-group">Group 6 : Database Technology</div>
                    <div style="margin-top: 0.5rem; font-size: 0.75rem; opacity: 0.7;">Surabaya 2025</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        localStorage.removeItem('dismissedBroadcasts');
    </script>
</body>
</html>
