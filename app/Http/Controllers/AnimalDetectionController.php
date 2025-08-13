<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class AnimalDetectionController extends Controller
{
    private $flaskApiUrl;
    private $timeout;

    public function __construct()
    {
        $this->flaskApiUrl = config('services.flask.url', 'http://127.0.0.1:5000');
        $this->timeout = config('services.flask.timeout', 60);
    }

    /**
     * Tampilkan halaman utama
     */
    public function index()
    {
        return view('animal-detection.index');
    }

    /**
     * Tampilkan riwayat deteksi
     */
    public function history()
    {
        // Implementasi riwayat deteksi
        return view('animal-detection.history');
    }

    /**
     * Proses deteksi hewan
     */
    public function detect(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240' // max 10MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 400);
            }

            $image = $request->file('image');
            
            // Cek koneksi ke Flask API
            $healthCheck = $this->checkFlaskApiHealth();
            if (!$healthCheck['status']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Flask API tidak tersedia. Pastikan server ML sedang berjalan di ' . $this->flaskApiUrl
                ], 503);
            }

            // Kirim gambar ke Flask API
            Log::info('Sending image to Flask API: ' . $this->flaskApiUrl . '/detect');
            
            $response = Http::timeout($this->timeout)
                ->attach('image', file_get_contents($image->getRealPath()), $image->getClientOriginalName())
                ->post($this->flaskApiUrl . '/detect');

            // Log response untuk debugging
            Log::info('Flask API Response Status: ' . $response->status());
            Log::info('Flask API Response Body: ' . $response->body());

            if ($response->successful()) {
                $result = $response->json();
                
                // Validasi response dari Flask
                if (!isset($result['success'])) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Response Flask API tidak valid'
                    ], 500);
                }

                if (!$result['success']) {
                    return response()->json([
                        'success' => false,
                        'error' => $result['error'] ?? 'Error tidak diketahui dari Flask API'
                    ], 500);
                }

                // Simpan gambar asli (opsional)
                $originalPath = $this->saveUploadedImage($image, 'originals');
                
                // Simpan gambar hasil deteksi jika ada
                $annotatedPath = null;
                if (isset($result['annotated_image'])) {
                    $annotatedPath = $this->saveBase64Image($result['annotated_image'], 'detections');
                }

                // Siapkan response
                $responseData = [
                    'success' => true,
                    'detections' => $result['detections'] ?? [],
                    'total_detections' => $result['total_detections'] ?? 0,
                    'annotated_image' => $result['annotated_image'] ?? null,
                    'original_path' => $originalPath,
                    'annotated_path' => $annotatedPath
                ];

                // Simpan ke database (opsional)
                // $this->saveDetectionRecord($responseData);

                return response()->json($responseData);

            } else {
                // Handle HTTP errors
                $errorMessage = 'Flask API error: ';
                
                if ($response->status() === 404) {
                    $errorMessage .= 'Endpoint tidak ditemukan. Pastikan Flask server berjalan dengan benar.';
                } elseif ($response->status() === 500) {
                    $errorMessage .= 'Internal server error di Flask API.';
                } elseif ($response->status() === 0) {
                    $errorMessage .= 'Tidak dapat terhubung ke Flask API. Pastikan server berjalan di ' . $this->flaskApiUrl;
                } else {
                    $errorMessage .= 'HTTP ' . $response->status() . ' - ' . $response->body();
                }

                Log::error('Flask API Error: ' . $errorMessage);

                return response()->json([
                    'success' => false,
                    'error' => $errorMessage
                ], $response->status() ?: 500);
            }

        } catch (Exception $e) {
            Log::error('Detection Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek status Flask API
     */
    public function checkFlaskApiHealth()
    {
        try {
            $response = Http::timeout(10)->get($this->flaskApiUrl . '/health');
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => true,
                    'data' => $data
                ];
            } else {
                return [
                    'status' => false,
                    'error' => 'Health check failed: HTTP ' . $response->status()
                ];
            }
        } catch (Exception $e) {
            Log::error('Flask Health Check Error: ' . $e->getMessage());
            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * API endpoint untuk cek health Flask
     */
    public function checkFlaskHealth()
    {
        $result = $this->checkFlaskApiHealth();
        return response()->json($result);
    }

    /**
     * Simpan gambar yang diupload
     */
    private function saveUploadedImage($file, $directory = 'uploads')
    {
        try {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/' . $directory, $filename);
            return Storage::url($path);
        } catch (Exception $e) {
            Log::error('Error saving uploaded image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Simpan gambar dari base64
     */
    private function saveBase64Image($base64String, $directory = 'detections')
    {
        try {
            $imageData = base64_decode($base64String);
            $filename = time() . '_detection_' . uniqid() . '.jpg';
            $path = 'public/' . $directory . '/' . $filename;
            
            Storage::put($path, $imageData);
            return Storage::url($path);
        } catch (Exception $e) {
            Log::error('Error saving base64 image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Simpan record deteksi ke database (opsional)
     */
    private function saveDetectionRecord($data)
    {
        try {
            // Implementasi jika menggunakan model Detection
            /*
            Detection::create([
                'original_image' => $data['original_path'],
                'annotated_image' => $data['annotated_path'],
                'detections_data' => json_encode($data['detections']),
                'total_detections' => $data['total_detections']
            ]);
            */
        } catch (Exception $e) {
            Log::error('Error saving detection record: ' . $e->getMessage());
        }
    }
}