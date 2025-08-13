<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detection extends Model
{
    protected $fillable = [
        'original_image',
        'annotated_image', 
        'detections_data',
        'total_detections',
        'created_at'
    ];

    protected $casts = [
        'detections_data' => 'array'
    ];
}