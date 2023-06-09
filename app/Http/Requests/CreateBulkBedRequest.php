<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBulkBedRequest extends FormRequest
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
        return [
            'name.*' => 'required|distinct|is_unique:beds,name',
            'bed_type.*' => 'required',
            //            'charge.*'   => 'required|numeric|min:0',
            'charge.*' => 'required|min:0/regex:/^[0-9]{1,3}(,[0-9]{3})*\.[0-9]+$/',
        ];
    }

    public function messages()
    {
        return [
            'name.*.distinct' => 'The Bed field has a duplicate value.',
            'name.*.is_unique' => 'The Bed :input has already been taken.',
            'charge.*.numeric' => 'The charge must be number.',
        ];
    }
}
