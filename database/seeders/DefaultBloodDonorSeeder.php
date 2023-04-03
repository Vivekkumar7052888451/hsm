<?php

namespace Database\Seeders;

use App\Models\BloodDonor;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;

class DefaultBloodDonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        $userTenantId = session('tenant_id', null);

        $bloodDonordata = [
            'name' => $faker->name(),
            'age' => $faker->numberBetween(1, 100),
            'blood_group' => 'B+',
            'last_donate_date' => Carbon::now(),
            'gender' => 1,
            'tenant_id' => $userTenantId,
        ];

        $bloodDonor = BloodDonor::create($bloodDonordata);
    }
}
