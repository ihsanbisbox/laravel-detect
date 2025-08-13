@extends('layouts.app')

@section('title', 'Sistem Deteksi Orangutan & Babi Hutan')

@section('content')
<div class="main-container">
    <!-- Header Section -->
    <div class="header-section">
        <div class="animal-icons">
            <i class="fas fa-paw"></i>
        </div>
        <h1><i class="fas fa-search me-3"></i>Sistem Deteksi Hewan</h1>
        <p>Deteksi Otomatis Orangutan & Babi Hutan menggunakan Teknologi AI</p>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="mx-4 mt-3"></div>

    <!-- Upload Section -->
    <div class="upload-card" id="uploadCard">
        <form id="detectionForm" enctype="multipart/form-data">
            @csrf
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <h4 class="mb-3">Upload Gambar untuk Deteksi</h4>
            <p class="text-muted mb-4">
                Drag & drop gambar di sini atau klik untuk memilih file<br>
                <small>Format yang didukung: JPG, PNG, GIF (Maksimal 10MB)</small>
            </p>
            
            <input type="file" 
                   id="imageInput" 
                   name="image" 
                   accept="image/*" 
                   style="display: none;"
                   required>
            
            <button type="button" 
                    class="btn btn-custom btn-lg" 
                    id="selectFileBtn">
                <i class="fas fa-folder-open me-2"></i>Pilih Gambar
            </button>
            
            <div id="fileInfo" class="mt-3" style="display: none;">
                <div class="alert alert-info alert-custom">
                    <i class="fas fa-file-image me-2"></i>
                    <span id="fileName"></span>
                    <span class="badge bg-secondary ms-2" id="fileSize"></span>
                </div>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-search me-2"></i>Mulai Deteksi
                </button>
                <button type="button" class="btn btn-outline-secondary ms-2" id="cancelBtn">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="progress-container" id="progressContainer">
                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         id="uploadProgress" 
                         role="progressbar" 
                         style="width: 0%">
                        0%
                    </div>
                </div>
                <p class="text-muted">
                    <i class="fas fa-cog fa-spin me-2"></i>
                    Sedang memproses gambar...
                </p>
            </div>
        </form>
    </div>

    <!-- Statistics Section -->
    <div class="animal-stats" id="statsSection" style="display: none;">
        <div class="stat-card">
            <div class="stat-icon orangutan-icon">
                <i class="fas fa-monkey"></i>
            </div>
            <h3 id="orangutanCount">0</h3>
            <p class="text-muted">Orangutan Terdeteksi</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon boar-icon">
                <i class="fas fa-pig"></i>
            </div>
            <h3 id="boarCount">0</h3>
            <p class="text-muted">Babi Hutan Terdeteksi</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon total-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3 id="totalCount">0</h3>
            <p class="text-muted">Total Deteksi</p>
        </div>
    </div>

    <!-- Result Section -->
    <div class="result-container" id="resultContainer">
        <div class="result-header">
            <h4><i class="fas fa-check-circle me-2"></i>Hasil Deteksi</h4>
        </div>
        
        <div class="image-comparison">
            <div class="image-box">
                <h6 class="mb-3">Gambar Asli</h6>
                <img id="originalImage" src="" alt="Original Image" class="img-fluid">
            </div>
            <div class="image-box">
                <h6 class="mb-3">Hasil Deteksi</h6>
                <img id="detectedImage" src="" alt="Detected Image" class="img-fluid">
            </div>
        </div>

        <div class="detection-details">
            <h5><i class="fas fa-list me-2"></i>Detail Deteksi</h5>
            <div id="detectionList">
                <!-- Detection items will be populated here -->
            </div>
        </div>

        <div class="text-center p-3">
            <button class="btn btn-custom me-2" id="downloadBtn">
                <i class="fas fa-download me-2"></i>Download Hasil
            </button>
            <button class="btn btn-outline-primary" id="newDetectionBtn">
                <i class="fas fa-plus me-2"></i>Deteksi Baru
            </button>
        </div>
    </div>

    <!-- Recent Detections Preview -->
    <div class="mx-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5><i class="fas fa-history me-2"></i>Deteksi Terbaru</h5>
            <a href="{{ route('detection.history') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-eye me-1"></i>Lihat Semua
            </a>
        </div>
        
        <div class="row" id="recentDetections">
            <!-- Recent detection cards will be loaded here -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedFile = null;

    // File input handler
    $('#selectFileBtn').click(function() {
        $('#imageInput').click();
    });

    $('#imageInput').change(function(e) {
        handleFileSelect(e.target.files[0]);
    });

    // Drag and drop handlers
    $('#uploadCard').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    $('#uploadCard').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    $('#uploadCard').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    // Handle file selection
    function handleFileSelect(file) {
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            showAlert('File yang dipilih bukan gambar!', 'danger');
            return;
        }

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            showAlert('Ukuran file terlalu besar! Maksimal 10MB.', 'danger');
            return;
        }

        selectedFile = file;
        $('#fileName').text(file.name);
        $('#fileSize').text(formatFileSize(file.size));
        $('#fileInfo').show();
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#originalImage').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    }

    // Cancel selection
    $('#cancelBtn').click(function() {
        selectedFile = null;
        $('#imageInput').val('');
        $('#fileInfo').hide();
        $('#resultContainer').hide();
        $('#statsSection').hide();
    });

    // Form submission
    $('#detectionForm').submit(function(e) {
        e.preventDefault();
        
        if (!selectedFile) {
            showAlert('Silakan pilih gambar terlebih dahulu!', 'warning');
            return;
        }

        performDetection();
    });

    // Perform detection
    function performDetection() {
        const formData = new FormData();
        formData.append('image', selectedFile);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        // Show progress
        $('#progressContainer').show();
        $('#uploadCard .btn').prop('disabled', true);
        
        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(function() {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            $('#uploadProgress').css('width', progress + '%').text(Math.round(progress) + '%');
        }, 500);

        // AJAX request to Laravel backend
        $.ajax({
            url: '{{ route("detection.detect") }}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                clearInterval(progressInterval);
                $('#uploadProgress').css('width', '100%').text('100%');
                
                setTimeout(function() {
                    $('#progressContainer').hide();
                    $('#uploadCard .btn').prop('disabled', false);
                    displayResults(response);
                }, 1000);
            },
            error: function(xhr, status, error) {
                clearInterval(progressInterval);
                $('#progressContainer').hide();
                $('#uploadCard .btn').prop('disabled', false);
                
                let errorMessage = 'Terjadi kesalahan saat memproses gambar.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                showAlert(errorMessage, 'danger');
            }
        });
    }

    // Display detection results
    function displayResults(response) {
        if (!response.success) {
            showAlert('Deteksi gagal: ' + response.error, 'danger');
            return;
        }

        // Update statistics
        let orangutanCount = 0;
        let boarCount = 0;
        
        response.detections.forEach(function(detection) {
            if (detection.class === 'orangutan') {
                orangutanCount++;
            } else if (detection.class === 'wild_boar') {
                boarCount++;
            }
        });

        $('#orangutanCount').text(orangutanCount);
        $('#boarCount').text(boarCount);
        $('#totalCount').text(response.total_detections);
        $('#statsSection').show();

        // Display annotated image
        $('#detectedImage').attr('src', 'data:image/jpeg;base64,' + response.annotated_image);

        // Display detection details
        let detectionHtml = '';
        if (response.detections.length === 0) {
            detectionHtml = '<div class="text-center text-muted py-4"><i class="fas fa-search fa-2x mb-3"></i><br>Tidak ada hewan yang terdeteksi dalam gambar ini.</div>';
        } else {
            response.detections.forEach(function(detection, index) {
                const confidencePercentage = Math.round(detection.confidence * 100);
                
                // Fix mapping sesuai dengan backend
                let animalIcon, animalName;
                if (detection.class === 'orangutan') {
                    animalIcon = 'fas fa-monkey orangutan-icon';
                    animalName = 'Orangutan';
                } else if (detection.class === 'wild_boar') {
                    animalIcon = 'fas fa-pig boar-icon';
                    animalName = 'Babi Hutan';
                } else {
                    animalIcon = 'fas fa-question-circle';
                    animalName = detection.class.charAt(0).toUpperCase() + detection.class.slice(1);
                }
                
                // Debug info jika ada
                let debugInfo = '';
                if (detection.model_class) {
                    debugInfo = `<br><small class="text-info">Model detected: ${detection.model_class} (class_id: ${detection.class_id})</small>`;
                }
                
                detectionHtml += `
                    <div class="detection-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">
                                <i class="${animalIcon} me-2"></i>${animalName} #${index + 1}
                            </h6>
                            <span class="badge bg-success">${confidencePercentage}% akurat</span>
                        </div>
                        <div class="confidence-bar">
                            <div class="confidence-fill" style="width: ${confidencePercentage}%"></div>
                        </div>
                        <small class="text-muted">
                            Koordinat: (${detection.bbox.x1}, ${detection.bbox.y1}) - (${detection.bbox.x2}, ${detection.bbox.y2})
                            ${debugInfo}
                        </small>
                    </div>
                `;
            });
        }
        
        $('#detectionList').html(detectionHtml);
        $('#resultContainer').show();

        // Show success message
        showAlert(`Deteksi berhasil! Ditemukan ${response.total_detections} hewan dalam gambar.`, 'success');
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#resultContainer').offset().top - 100
        }, 1000);
    }

    // New detection
    $('#newDetectionBtn').click(function() {
        selectedFile = null;
        $('#imageInput').val('');
        $('#fileInfo').hide();
        $('#resultContainer').hide();
        $('#statsSection').hide();
        $('#alertContainer').html('');
        
        $('html, body').animate({
            scrollTop: $('#uploadCard').offset().top - 100
        }, 1000);
    });

    // Download result
    $('#downloadBtn').click(function() {
        if ($('#detectedImage').attr('src')) {
            const link = document.createElement('a');
            link.download = 'detection_result_' + new Date().getTime() + '.jpg';
            link.href = $('#detectedImage').attr('src');
            link.click();
        }
    });

    // Load recent detections
    loadRecentDetections();
});

function loadRecentDetections() {
    // This would typically load from your Laravel backend
    // For now, we'll show a placeholder
    $('#recentDetections').html(`
        <div class="col-12 text-center text-muted py-4">
            <i class="fas fa-image fa-2x mb-3"></i><br>
            Belum ada riwayat deteksi. Mulai dengan mengupload gambar pertama Anda!
        </div>
    `);
}
</script>
@endpush