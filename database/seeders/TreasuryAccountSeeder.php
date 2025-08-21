<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TreasuryAccountSeeder extends Seeder
{
    private const TOTAL = 100_000;
    private const BATCH = 1000;

    public function run(): void
    {
        $faker = Faker::create();
        $currencies = ['UZS','USD','EUR','RUB','KZT','CNY'];
        $now = now()->toDateTimeString();

        DB::disableQueryLog();

        $userIds = DB::table('users')->pluck('id')->toArray();

        $batch = [];

        for ($i = 0; $i < self::TOTAL; $i++) {
            $account = 'AC' . str_pad((string)$i, 26, '0', STR_PAD_LEFT);

            $mfo = str_pad((string)random_int(0, 999_999_999), 9, '0', STR_PAD_LEFT);

            $batch[] = [
                'account'    => $account,
                'mfo'        => $mfo,
                'name'       => $faker->company(),
                'department' => $faker->optional(0.4)->company(),
                'currency'   => $currencies[array_rand($currencies)],
                'created_by' => $faker->randomElement($userIds),
                'updated_by' => $faker->randomElement($userIds),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (\count($batch) === self::BATCH) {
                DB::table('treasury_accounts')->insert($batch);
                $batch = [];
            }
        }

        if ($batch) {
            DB::table('treasury_accounts')->insert($batch);
        }
    }
}
