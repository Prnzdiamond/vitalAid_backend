<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Dr. Tunde Alabi',
                'email' => 'tunde.alabi@funaab.edu.ng',
                'role' => 'community_organizer',
            ],
            [
                'name' => 'Blessing Adeyemi',
                'email' => 'blessing.adeyemi@gmail.com',
                'role' => 'individual',
            ],
            [
                'name' => 'FUNAAB Muslim Students\' Society',
                'email' => 'mssn@funaab.edu.ng',
                'role' => 'organization',
            ],
            [
                'name' => 'Adeola Okeowo',
                'email' => 'adeola.okeowo@yahoo.com',
                'role' => 'individual',
            ],
            [
                'name' => 'GreenFuture Club FUNAAB',
                'email' => 'greenfuture@funaab.edu.ng',
                'role' => 'organization',
            ],
            [
                'name' => 'Sola Ibrahim',
                'email' => 'sola.ibrahim@gmail.com',
                'role' => 'volunteer',
            ],
            [
                'name' => 'Ngozi Nwachukwu',
                'email' => 'ngozi.nwachukwu@hotmail.com',
                'role' => 'individual',
            ],
            [
                'name' => 'Abeokuta Youth Empowerment',
                'email' => 'aye@ayenetwork.org',
                'role' => 'organization',
            ],
            [
                'name' => 'Pastor Samuel Ajayi',
                'email' => 'samuel.ajayi@churchmail.org',
                'role' => 'community_leader',
            ],
            [
                'name' => 'FUNAAB Women’s Initiative',
                'email' => 'fwi@funaab.edu.ng',
                'role' => 'organization',
            ],
        ];

        foreach ($users as $userData) {
            $userData['password'] = Hash::make('12345678');
            $userData['verification_status'] = 'approved';
            $userData['_tag'] = '@' . Str::slug($userData['name'], '_') . '_' . Str::random(4);
            $userData['verification_approved_at'] = now();
            $userData['verification_approved_by'] = 'system';
            $userData['verification_documents'] = [];
            $userData['verification_progress'] = 0;
            $userData['is_verified'] = true;

            User::create($userData);
        }
    }
}
