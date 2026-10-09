<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - OPTIMA FINANCE</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --optima-primary: #1e3a8a;
            --optima-primary-hover: #1e40af;
            --optima-accent: #3b82f6;
            --optima-bg: #0f172a;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #090d16 100%);
            min-height: 100vh;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: rgba(30, 41, 59, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .form-control {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #f8fafc;
            border-radius: 0.625rem;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background-color: rgba(15, 23, 42, 0.85);
            border-color: var(--optima-accent);
            color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .form-control::placeholder {
            color: #94a3b8;
        }
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            border-radius: 0.625rem;
            transition: all 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }
        .brand-badge {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.4);
        }
        .role-pill {
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
        }
        .role-pill:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        @yield('content')
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
