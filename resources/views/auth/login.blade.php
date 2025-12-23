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
            --bg-page: #f8fafc;
            --primary: #3b82f6;
            --text-dark: #0f172a;
            --text-medium: #475569;
            --text-light: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
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
            background: linear-gradient(to right, #fff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
            width: 48px; height: 48px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
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
            overflow-y: auto; /* Allow scrolling on mobile */
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 2rem 0;
        }

        .brand-logo { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .welcome-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; }
        .welcome-subtitle { font-size: 0.95rem; color: var(--text-medium); margin-bottom: 2rem; }

        .google-btn {
            display: flex; align-items: center; justify-content: center; gap: 12px;
            width: 100%; padding: 1rem;
            background-color: white; color: var(--text-medium);
            border: 1px solid #e2e8f0; border-radius: 12px;
            font-size: 1rem; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .google-btn:hover {
            border-color: #cbd5e1; background-color: #f8fafc;
            transform: translateY(-1px); color: var(--text-dark);
        }

        .alert-error {
            background-color: #fef2f2; border: 1px solid #fee2e2; color: #ef4444;
            padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;
            font-size: 0.9rem; display: flex; align-items: center; gap: 0.75rem;
        }

        /* Footer Contacts */
        .footer-contacts {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
        }
        .footer-title {
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--text-light); font-weight: 600; margin-bottom: 0.75rem;
            text-align: center;
        }
        .contact-list {
            display: flex; flex-direction: column; gap: 0.4rem;
            font-size: 0.8rem;
        }
        .contact-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.4rem 0.6rem; border-radius: 6px;
            color: var(--text-medium); text-decoration: none;
            transition: background 0.2s;
        }
        .contact-item:hover { background: #f8fafc; color: var(--primary); }
        .nrp { font-size: 0.7rem; color: var(--text-light); font-family: monospace; }

        /* Dept Footer */
        .dept-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            color: var(--text-light);
            font-size: 0.75rem;
            line-height: 1.5;
        }

        /* Mobile Responsive */
        @media (max-width: 960px) {
            .container { flex-direction: column; }
            
            .visual-side {
                padding: 3rem 2rem;
                flex: none; /* Auto height based on content */
            }

            .hero-title { font-size: 2rem; }
            .feature-text p { display: none; } /* Provide cleaner look on mobile */
            .feature-item { align-items: center; }
            .features-list { gap: 1rem; margin-top: 1rem; }
            .feature-icon { width: 36px; height: 36px; font-size: 1rem; }
            .feature-text h3 { font-size: 0.95rem; margin: 0; }
            
            .login-side { padding: 2rem; flex: 1; }
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
                        <div class="feature-icon">�</div>
                        <div class="feature-text">
                            <h3>Real-time GIS Tracking</h3>
                            <p>Global fleet monitoring & route optimization.</p>
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
                        <div class="feature-icon">�</div>
                        <div class="feature-text">
                            <h3>Role-Based Security</h3>
                            <p>Secure access for every stakeholder level.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Side -->
        <div class="login-side">
            <div class="login-wrapper">
                <div class="brand-logo">📦</div>
                <h2 class="welcome-title">Sign In</h2>
                <p class="welcome-subtitle">Seamlessly manage your logistics network.</p>

                @if(session('error'))
                    <div class="alert-error">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <a href="{{ route('auth.google') }}" class="google-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with ITS Google Account
                </a>

                <div style="margin-top: 1.5rem; text-align: center; font-size: 0.75rem; color: #94a3b8;">
                    Use your institutional account to continue.
                </div>

                <!-- Superadmin Contacts -->
                <div class="footer-contacts">
                    <div class="footer-title">Contact Superadmin</div>
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
                    <div>Department of Mathematics ITS</div>
                    <div>Group 6 : Database Technology</div>
                    <div style="margin-top: 0.25rem; font-weight: 600;">Surabaya 2025</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        localStorage.removeItem('dismissedBroadcasts');
    </script>
</body>
</html>
