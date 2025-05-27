<?php

// namespace Database\Seeders;

// use App\Models\Plan;
// use App\Models\PromoCode;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;

// class PromoCodeSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run()
//     {
//         $promo = PromoCode::create([
//             'code' => 'WELCOME20',
//             'type' => 'percent',
//             'value' => 20,
//             'valid_to' => now()->addDays(30),
//             'max_uses' => 100,
//         ]);

//         // Apply to all plans
//         $promo->plans()->attach(Plan::pluck('id'));
//     }
// }
