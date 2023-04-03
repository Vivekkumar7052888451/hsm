<?php

namespace App\Repositories;

use App\Models\HospitalType;
use App\Repositories\BaseRepository;

/**
 * Class HospitalTypeRepository
 * @package App\Repositories
 * @version September 5, 2022, 8:14 pm UTC
*/

class HospitalTypeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'name'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return HospitalType::class;
    }
}
