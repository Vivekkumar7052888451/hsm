<?php
namespace App\Http\Livewire;
use App\Models\EmergencyRoom;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;
use Rappasoft\LaravelLivewireTables\Views\Column;

class EmergencyRoomTable extends LivewireTableComponent
{
    use WithPagination;
    public $showButtonOnHeader = true;

    public $showFilterOnHeader = true;

    public $paginationIsEnabled = true;

   public $buttonComponent = 'er.add-button';

    public $FilterComponent = ['er.filter-button', EmergencyRoom::FILTER_STATUS_ARR];

    protected $model = EmergencyRoom::class;
    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        
        return [
            Column::make(__('messages.emergency.er_number'), 'er_number')
                ->view('er.columns.er_number')
                ->sortable()
                ->searchable(),
            Column::make(__('messages.emergency.er_patient_id'), 'patient_id')
                ->hideIf('patient.patientUser.first_name')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.emergency.er_patient_id'), 'patient_id')
                ->hideIf('patient.patientUser.first_name')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.emergency.er_patient_id'), 'patient.patientUser.first_name')
                ->view('er.columns.patient')
                ->sortable(),
            Column::make(__('messages.emergency.er_bed_id'), 'bed_id')
                ->hideIf('bed_id')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.emergency.er_doctor_id'), 'doctor_id')
                ->hideIf('doctor.doctorUser.email')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.emergency.er_doctor_id'), 'doctor.doctorUser.first_name')
                ->view('er.columns.doctor')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.emergency.er_admission_date'), 'admission_date')
                ->view('er.columns.admission_date')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.emergency.er_bed_id'), 'bed.name')
                ->view('er.columns.bed')
                ->searchable()
                ->sortable(),
            Column::make(__('messages.emergency.er_bill_status'), 'bill_status')
                ->view('er.columns.bill_status'),
            Column::make(__('messages.common.action'), 'id')
                ->view('er.action'),
        ];
    }

    public function builder(): Builder
    {
        $query = EmergencyRoom::select('emergency_rooms.*');
        return $query;
    }
}
