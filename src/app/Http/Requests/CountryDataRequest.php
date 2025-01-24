<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountryDataRequest extends FormRequest
{
    // Authorize the request (or add your own logic)
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // The country can be one of several codes or null
            'country' => 'nullable|string|in:gb,nl,de,fr,es,it,gr',
            'page' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:1',
            'force_refresh' => 'nullable|boolean'
        ];
    }
}
