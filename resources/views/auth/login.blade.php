<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Supply Chain Management Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;        /* Enterprise Blue */
            --primary-dark: #1e40af;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--background);
            color: var(--text-main);
            overflow-x: hidden;
            position: relative;
        }

        /* Subtle Logistics/Network Background */
        .bg-network {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #0f172a; /* Slate 900 */
            background-image: 
                radial-gradient(at 40% 20%, rgba(37, 99, 235, 0.15) 0px, transparent 50%),
                radial-gradient(at 80% 0%, rgba(14, 165, 233, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        /* Animated connected nodes CSS effect */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            animation: moveParticle 20s infinite linear;
        }
        @keyframes moveParticle {
            0% { transform: translateY(100vh) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-20vh) translateX(20vw); opacity: 0; }
        }

        /* Main Layout */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 3rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            text-align: center;
        }

        .logo-area {
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            font-size: 2rem;
        }
        
        .app-name {
            font-weight: 700;
            font-size: 1.25rem;
            color: #0f172a;
            letter-spacing: -0.025em;
        }

        .title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 2.5rem;
            line-height: 1.5;
        }

        /* Google Button */
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 0.75rem 1.5rem;
            background-color: #ffffff;
            color: #374151;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            font-family: 'Roboto', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .google-btn:hover {
            background-color: #f8fafc;
            box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
            border-color: #d2e3fc;
        }

        .google-btn:active {
            background-color: #f1f5f9;
        }

        .google-icon {
            width: 18px;
            height: 18px;
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #ef4444;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: left;
        }

        /* Footer */
        .footer {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            color: #94a3b8;
            padding: 1.5rem 2rem;
            font-size: 0.8rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            position: relative;
            z-index: 10;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .footer-title {
            color: #e2e8f0;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
        }

        .contact-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-link {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .contact-link:hover {
            color: #60a5fa;
            text-decoration: underline;
        }

        .student-id {
            opacity: 0.6;
            font-size: 0.75rem;
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 2rem;
            }
            .contact-grid {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-network">
        <!-- Particles for animated effect -->
        <div class="particles">
            <div class="particle" style="top: 10%; left: 20%; animation-duration: 15s; animation-delay: 0s;"></div>
            <div class="particle" style="top: 30%; left: 80%; animation-duration: 25s; animation-delay: 2s;"></div>
            <div class="particle" style="top: 70%; left: 40%; animation-duration: 20s; animation-delay: 5s;"></div>
            <div class="particle" style="top: 40%; left: 10%; animation-duration: 18s; animation-delay: 1s;"></div>
            <div class="particle" style="top: 80%; left: 90%; animation-duration: 22s; animation-delay: 3s;"></div>
        </div>
    </div>

    <main class="main-content">
        <div class="login-card">
            <div class="logo-area">
                <span class="logo-icon">🌐</span>
                <span class="app-name">Supply Chain Portal</span>
            </div>

            <h1 class="title">Welcome Back</h1>
            <p class="subtitle">Use your ITS Google account to continue to the dashboard.</p>

            @if(session('error'))
                <div class="alert-error">
                    {{ session('error') }}
                </div>
            @endif

            <a href="{{ route('auth.google') }}" class="google-btn">
                <svg class="google-icon" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.64 9.2c0-.637-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"></path>
                    <path d="M9 18c2.43 0 4.467-.806 5.956-2.18L12.048 13.56c-.806.54-1.836.86-3.048.86-2.344 0-4.328-1.584-5.036-3.715H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"></path>
                    <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"></path>
                    <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.157 6.656 3.58 9 3.58z" fill="#EA4335"></path>
                </svg>
                <span>Continue with Google</span>
            </a>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-title">Contact Superadmin for Website Access</div>
            <div class="contact-grid">
                <div class="contact-item">
                    <a href="mailto:5002221003@student.its.ac.id" class="contact-link" title="Send email to Nicholas">
                        <span>Nicholas Joe Sumantri</span>
                        <span class="student-id">(5002221003)</span>
                    </a>
                </div>
                <div class="contact-item">
                    <a href="mailto:50002221041@student.its.ac.id" class="contact-link" title="Send email to Nabilah">
                        <span>Nabilah Safa Nur Fatimah</span>
                        <span class="student-id">(50002221041)</span>
                    </a>
                </div>
                <div class="contact-item">
                    <a href="mailto:5002221055@student.its.ac.id" class="contact-link" title="Send email to Marsyanda">
                        <span>Marsyanda Auditya</span>
                        <span class="student-id">(5002221055)</span>
                    </a>
                </div>
                <div class="contact-item">
                    <a href="mailto:5002221084@student.its.ac.id" class="contact-link" title="Send email to Moch Fajar">
                        <span>Moch Fajar Aditya Putra</span>
                        <span class="student-id">(5002221084)</span>
                    </a>
                </div>
                <div class="contact-item">
                    <a href="mailto:5002221085@student.its.ac.id" class="contact-link" title="Send email to Prasasti">
                        <span>Prasasti Intan Pratiwi</span>
                        <span class="student-id">(5002221085)</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Clear dismissed broadcasts when user visits login page (after logout)
        // This ensures all broadcasts show again when user logs back in
        localStorage.removeItem('dismissedBroadcasts');
    </script>
</body>
</html>
