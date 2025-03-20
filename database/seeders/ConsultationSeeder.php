<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VitalAid\Consultation;


class ConsultationSeeder extends Seeder
{
    public function run()
    {
        Consultation::create([
            'user_id' => null,
            'doctor_id' => null,
            'messages' => [],
            'status' => 'in_progress'

        ]);
    }
}