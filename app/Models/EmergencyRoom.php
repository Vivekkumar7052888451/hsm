<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Eloquent as Model;
use Str;
class EmergencyRoom extends Model
{
    protected $table='emergency_rooms';
    use HasFactory;

    const FILTER_STATUS_ARR = [
        0 => 'All',
        1 => 'Active',
        2 => 'Deactive',
    ];
const STATUS_ARR = [
        '' => 'All',
        0 => 'Active',
        1 => 'Discharged',
    ];

    public $fillable = [
        'patient_id',
        'er_number',
        'height',
        'weight',
        'bp',
        'symptoms',
        'notes',
        'admission_date',
        'case_id',
        'is_old_patient',
        'doctor_id',
        'bed_type_id',
        'bed_id',
    ];
      public static $rules = [
        'patient_id' => 'required',
        'case_id' => 'required',
        'admission_date' => 'required',
        'doctor_id' => 'required',
        'bed_type_id' => 'required',
        'bed_id' => 'required',
        'weight' => 'numeric|max:200',
        'height' => 'numeric|max:7',
    ];
public function getAssociatedData()
    {
        $data['patients'] = Patient::with('patientUser')->get()->where('patientUser.status', '=', 1)->pluck('patientUser.full_name',
            'id')->sort();
        $data['doctors'] = Doctor::with('doctorUser')->get()->where('doctorUser.status', '=', 1)->pluck('doctorUser.full_name',
            'id')->sort();
        $data['bedTypes'] = BedType::pluck('title', 'id')->toArray();
        natcasesort($data['bedTypes']);
        $data['erNumber'] = $this->model->generateUniqueErNumber();
        return $data;
    }
     public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bed_id');
    }
     public function patientCase()
    {
        return $this->belongsTo(PatientCase::class, 'case_id');
    }
    public static function generateUniqueErNumber()
    {
        $erNumber = strtoupper(Str::random(8));
        while (true) {
            $isExist = self::whereErNumber($erNumber)->exists();
            if ($isExist) {
                self::generateUniqueErNumber();
            }
            break;
        }

        return $erNumber;
    }
}
