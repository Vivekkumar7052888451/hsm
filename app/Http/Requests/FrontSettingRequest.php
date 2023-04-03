<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FrontSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->request->get('sectionName') == 'cms') {
            return [
                'home_page_image' => 'mimes:jpeg,png,jpg,webp',
                'home_page_certified_doctor_image' => 'mimes:jpeg,png,jpg,webp',
            ];
        }

        if ($this->request->get('sectionName') == 'about-us') {
            return [
                'about_us_image' => 'mimes:jpeg,png,jpg,webp',
            ];
        }

        return [];
    }
}
