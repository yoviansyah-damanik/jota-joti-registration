<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(SettingService $settings): void
    {
        // Akun admin selalu di-seed pada setiap environment
        User::updateOrCreate(
            ['username' => env('ADMIN_USERNAME', 'admin')],
            [
                'name' => 'Administrator',
                'email' => env('ADMIN_EMAIL', 'admin@jotajoti.test'),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'is_admin' => true,
            ],
        );

        // Ketika environment production, hentikan proses (hanya akun admin yang di-seed)
        if (app()->environment('production', 'prod')) {
            return;
        }

        // Ketika environment local atau dev, lakukan seed seluruh data sample
        if (app()->environment('local', 'dev', 'development')) {
            $settings->save([
                ...SettingService::DEFAULTS,
                'registration_fee' => '50000',
                'bank_account_info' => "Bank BRI 0000-00-000000-00-0\na.n. Panitia JOTA JOTI Kwarcab Kota Padangsidimpuan",
                'contact_info' => 'Panitia: 0812-0000-0000 (WhatsApp)',
                'additional_terms' => "Setiap gudep wajib mengirimkan minimal jumlah peserta sesuai ketentuan.\nPeserta wajib memiliki KTA (Kartu Tanda Anggota) Pramuka yang masih berlaku.\nPembayaran dilakukan sebelum pendaftaran dan bukti transfer diunggah pada formulir.",
            ]);

            $this->call(DemoSeeder::class);
        }
    }
}
