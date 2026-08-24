<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StaffUsersSeeder extends Seeder
{
    public static function accountGroups(): array
    {
        return [
            'Community Manager' => collect(range(1, 5))
                ->map(fn (int $number) => [
                    'name' => sprintf('Community Manager %02d', $number),
                    'email' => sprintf('community.manager%02d@prodovidigital.com', $number),
                ])
                ->all(),
            'Diseñador' => collect(range(1, 12))
                ->map(fn (int $number) => [
                    'name' => sprintf('Diseñador %02d', $number),
                    'email' => sprintf('disenador%02d@prodovidigital.com', $number),
                ])
                ->all(),
            'Administrador' => [[
                'name' => 'Administrador Operativo',
                'email' => 'administrador.operativo@prodovidigital.com',
            ]],
        ];
    }

    public function run(): void
    {
        $password = config('seeding.staff_password');

        if (! is_string($password) || mb_strlen($password) < 12) {
            throw new RuntimeException('Configura una contraseña de al menos 12 caracteres para crear el equipo.');
        }

        $this->call(RolesSeeder::class);

        DB::transaction(function () use ($password) {
            foreach (self::accountGroups() as $roleName => $accounts) {
                $role = Role::withTrashed()->firstOrNew(['nombre_rol' => $roleName]);
                $role->deleted_at = null;
                $role->save();

                foreach ($accounts as $account) {
                    $user = User::withTrashed()->firstOrNew(['email' => $account['email']]);
                    $user->name = $account['name'];
                    $user->password = $password;
                    $user->email_verified_at = now();
                    $user->deleted_at = null;
                    $user->save();
                    $user->roles()->sync([$role->id]);
                }
            }
        });

        $this->command?->info('Equipo creado: 5 Community Managers, 12 Diseñadores y 1 Administrador.');
    }
}
