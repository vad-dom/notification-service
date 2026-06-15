<?php

namespace Database\Seeders;

use App\Models\Recipient;
use Illuminate\Database\Seeder;

class RecipientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 10) as $number) {
            Recipient::query()->updateOrCreate(
                ['external_id' => 'recipient-'.$number],
                [
                    'email' => 'recipient'.$number.'@example.com',
                    'phone' => '+799900000'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                ]
            );
        }
    }
}
