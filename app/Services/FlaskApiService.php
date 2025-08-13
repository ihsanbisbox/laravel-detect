<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlaskApiService
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.flask.url');
    }

    public function detectAnimals($imageFile)
    {
        // Komunikasi dengan Flask API
    }

    public function checkHealth()
    {
        // Cek status Flask server
    }
}