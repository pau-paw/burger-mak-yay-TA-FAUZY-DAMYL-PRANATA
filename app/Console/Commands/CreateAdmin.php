<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Buat admin baru dengan kata sandi privat, tanpa kredensial default';

    public function handle(): int
    {
        $data = [
            'name' => $this->ask('Nama admin'),
            'email' => mb_strtolower(trim((string) $this->ask('Email admin baru'))),
            'password' => $this->secret('Kata sandi (minimal 10 karakter, huruf dan angka)'),
            'password_confirmation' => $this->secret('Ulangi kata sandi'),
        ];
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'max:128', Password::min(10)->letters()->numbers()],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        $user = new User;
        $user->fill($validator->validated());
        $user->role = 'admin';
        $user->save();
        $this->info('Admin berhasil dibuat. Masuk melalui /login.');

        return self::SUCCESS;
    }
}
