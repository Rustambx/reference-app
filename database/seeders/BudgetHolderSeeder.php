<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class BudgetHolderSeeder extends Seeder
{
    private const TOTAL = 100_000;
    private const BATCH_SIZE = 1000;

    public function run(): void
    {
        $faker = Faker::create();

        $regions = [
            'Tashkent', 'Samarkand', 'Bukhara', 'Andijan', 'Namangan', 'Fergana',
            'Karakalpakstan', 'Khorezm', 'Kashkadarya', 'Surkhandarya', 'Jizzakh', 'Sirdarya', 'Navoi'
        ];

        $batch = [];
        $tinBase = 10_000_000_000_000;

        DB::disableQueryLog();

        $userIds = DB::table('users')->pluck('id')->toArray();

        for ($i = 0; $i < self::TOTAL; $i++) {
            $tin = (string) ($tinBase + $i);
            $name = $faker->company();
            $region = $faker->randomElement($regions);
            $district = $faker->city();
            $address = $faker->streetAddress();

            $phone = '+998' . $faker->numerify('#########');

            $batch[] = [
                'tin'         => $tin,
                'name'        => $name,
                'region'      => $region,
                'district'    => $district,
                'address'     => $address,
                'phone'       => $phone,
                'responsible' => $faker->name(),
                'created_by'  => $faker->randomElement($userIds),
                'updated_by'  => $faker->randomElement($userIds),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            if (count($batch) === self::BATCH_SIZE) {
                DB::table('budget_holders')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('budget_holders')->insert($batch);
        }
    }
}
