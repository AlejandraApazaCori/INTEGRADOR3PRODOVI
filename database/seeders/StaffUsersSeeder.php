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
            'Community Manager' => [
                ['name' => 'Carla Mendoza', 'email' => 'carla_mendoza_cm@prodovidigital.com', 'legacy_email' => 'community.manager01@prodovidigital.com'],
                ['name' => 'Diego Rojas', 'email' => 'diego_rojas_cm@prodovidigital.com', 'legacy_email' => 'community.manager02@prodovidigital.com'],
                ['name' => 'Valeria Quiroga', 'email' => 'valeria_quiroga_cm@prodovidigital.com', 'legacy_email' => 'community.manager03@prodovidigital.com'],
                ['name' => 'Andrés Vargas', 'email' => 'andres_vargas_cm@prodovidigital.com', 'legacy_email' => 'community.manager04@prodovidigital.com'],
                ['name' => 'Sofía Castro', 'email' => 'sofia_castro_cm@prodovidigital.com', 'legacy_email' => 'community.manager05@prodovidigital.com'],
            ],
            'Diseñador' => [
                ['name' => 'Manuel Paye', 'email' => 'manuel_paye_disenador@prodovidigital.com', 'legacy_email' => 'disenador01@prodovidigital.com'],
                ['name' => 'Camila Flores', 'email' => 'camila_flores_disenador@prodovidigital.com', 'legacy_email' => 'disenador02@prodovidigital.com'],
                ['name' => 'Mateo Condori', 'email' => 'mateo_condori_disenador@prodovidigital.com', 'legacy_email' => 'disenador03@prodovidigital.com'],
                ['name' => 'Daniela Paredes', 'email' => 'daniela_paredes_disenador@prodovidigital.com', 'legacy_email' => 'disenador04@prodovidigital.com'],
                ['name' => 'Nicolás Mamani', 'email' => 'nicolas_mamani_disenador@prodovidigital.com', 'legacy_email' => 'disenador05@prodovidigital.com'],
                ['name' => 'Fernanda Salazar', 'email' => 'fernanda_salazar_disenador@prodovidigital.com', 'legacy_email' => 'disenador06@prodovidigital.com'],
                ['name' => 'Gabriel Choque', 'email' => 'gabriel_choque_disenador@prodovidigital.com', 'legacy_email' => 'disenador07@prodovidigital.com'],
                ['name' => 'Mariana Alarcón', 'email' => 'mariana_alarcon_disenador@prodovidigital.com', 'legacy_email' => 'disenador08@prodovidigital.com'],
                ['name' => 'Sebastián Lima', 'email' => 'sebastian_lima_disenador@prodovidigital.com', 'legacy_email' => 'disenador09@prodovidigital.com'],
                ['name' => 'Luciana Arce', 'email' => 'luciana_arce_disenador@prodovidigital.com', 'legacy_email' => 'disenador10@prodovidigital.com'],
                ['name' => 'Rodrigo Villca', 'email' => 'rodrigo_villca_disenador@prodovidigital.com', 'legacy_email' => 'disenador11@prodovidigital.com'],
                ['name' => 'Paola Gutiérrez', 'email' => 'paola_gutierrez_disenador@prodovidigital.com', 'legacy_email' => 'disenador12@prodovidigital.com'],
            ],
            'Administrador' => [[
                'name' => 'Lucía Fernández',
                'email' => 'lucia_fernandez_administrador@prodovidigital.com',
                'legacy_email' => 'administrador.operativo@prodovidigital.com',
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
                    $user = User::withTrashed()->where('email', $account['email'])->first()
                        ?? User::withTrashed()->where('email', $account['legacy_email'])->first()
                        ?? new User();
                    $user->name = $account['name'];
                    $user->email = $account['email'];
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
