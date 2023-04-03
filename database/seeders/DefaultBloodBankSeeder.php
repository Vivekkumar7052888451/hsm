<?php

namespace Database\Seeders;

use App\Models\BloodBank;
use Faker\Factory;
use Illuminate\Database\Seeder;

class DefaultBloodBankSeeder extends Seeder
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

        $bloodBankData = [
            'blood_group' => 'B+',
            'remained_bags' => $faker->numberBetween(1, 100),
            'tenant_id' => $userTenantId,
        ];

        $bloodBank = BloodBank::create($bloodBankData);
    }
}
