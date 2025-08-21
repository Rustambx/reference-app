<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SwiftSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $rows = [];
        $now  = Carbon::now()->toDateTimeString();
        $userIds = DB::table('users')->pluck('id')->toArray();

        for ($i = 0; $i < 100000; $i++) {
            $swiftCode = strtoupper(
                $faker->lexify('????')
                . $faker->countryCode
                . $faker->lexify('??')
            );

            if ($faker->boolean(30)) {
                $swiftCode .= strtoupper($faker->lexify('???'));
            }

            $rows[] = [
                'swift_code' => substr($swiftCode, 0, 11),
                'bank_name'  => $faker->company . ' Bank',
                'country'    => $faker->countryISOAlpha3,
                'city'       => $faker->city,
                'address'    => $faker->address,
                'created_by' => $faker->randomElement($userIds),
                'updated_by' => $faker->randomElement($userIds),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) === 1000) {
                DB::table('swifts')->insert($rows);
                $rows = [];
            }
        }

        if ($rows) {
            DB::table('swifts')->insert($rows);
        }
    }
}
