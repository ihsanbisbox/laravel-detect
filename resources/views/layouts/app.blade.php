<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Deteksi Hewan')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #8b4513;
            --accent-color: #ff6b35;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 1200px;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .header-section {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            text-align: center;
            padding: 40px 20px;
            position: relative;
            overflow: hidden;
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><pattern id="grain" width="100" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1.5" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .header-section h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }

        .header-section p {
            font-size: 1.2rem;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .animal-icons {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 3rem;
            opacity: 0.2;
        }

        .upload-card {
            background: var(--light-bg);
            border: 3px dashed #dee2e6;
            border-radius: 20px;
            padding: 40px;
            margin: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .upload-card:hover {
            border-color: var(--primary-color);
            background: #f0f8f0;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .upload-card.dragover {
            border-color: var(--accent-color);
            background: #fff5f0;
        }

        .upload-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .upload-card:hover .upload-icon {
            transform: scale(1.1);
        }

        .progress-container {
            display: none;
            margin: 20px 0;
        }

        .animal-stats {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 10px;
            flex: 1;
            min-width: 200px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .orangutan-icon {
            color: #ff6b35;
        }

        .boar-icon {
            color: var(--secondary-color);
        }

        .total-icon {
            color: var(--primary-color);
        }

        .result-container {
            background: white;
            border-radius: 15px;
            margin: 30px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .result-header {
            background: var(--success-color);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .image-comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px;
        }

        .image-box {
            text-align: center;
        }

        .image-box img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .detection-details {
            background: var(--light-bg);
            padding: 20px;
            margin: 20px;
            border-radius: 10px;
        }

        .detection-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .confidence-bar {
            background: #e9ecef;
            border-radius: 10px;
            height: 10px;
            margin: 10px 0;
            overflow: hidden;
        }

        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--warning-color), var(--success-color));
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .btn-custom {
            background: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            background: #1e3a21;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .spinner-border-sm {
            margin-right: 10px;
        }

        .alert-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .image-comparison {
                grid-template-columns: 1fr;
            }
            
            .animal-stats {
                flex-direction: column;
            }
            
            .header-section h1 {
                font-size: 2rem;
            }
            
            .main-container {
                margin: 10px;
                border-radius: 15px;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('detection.index') }}">
                <i class="fas fa-paw me-2"></i>Animal Detection
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('detection.index') }}">
                            <i class="fas fa-home me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('detection.history') }}">
                            <i class="fas fa-history me-1"></i>Riwayat
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    @yield('content')

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
            <h5>Sedang Memproses Deteksi...</h5>
            <p class="text-muted">Mohon tunggu, sistem sedang menganalisis gambar Anda</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Common JavaScript -->
    <script>
        // Setup CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Show loading overlay
        function showLoading() {
            $('#loadingOverlay').css('display', 'flex');
        }

        // Hide loading overlay
        function hideLoading() {
            $('#loadingOverlay').hide();
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Show alert message
        function showAlert(message, type = 'success') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show alert-custom" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#alertContainer').html(alertHtml);
        }
    </script>
    
    @stack('scripts')
</body>
</html>