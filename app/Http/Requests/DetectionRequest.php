<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetectionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240'
        ];
    }
}