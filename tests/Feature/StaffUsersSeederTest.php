<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\StaffUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffUsersSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_expected_staff_without_a_super_administrator(): void
    {
        config(['seeding.staff_password' => 'Temporal#Equipo2026']);

        $this->seed(StaffUsersSeeder::class);

        $this->assertSame(5, User::whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Community Manager'))->count());
        $this->assertSame(12, User::whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Diseñador'))->count());
        $this->assertSame(1, User::whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Administrador'))->count());

        $administrator = User::where('email', 'administrador.operativo@prodovidigital.com')->firstOrFail();

        $this->assertSame(['Administrador'], $administrator->roles->pluck('nombre_rol')->all());
        $this->assertTrue(Hash::check('Temporal#Equipo2026', $administrator->password));
        $this->assertFalse(User::whereHas('roles', fn ($query) => $query->where('nombre_rol', 'Super Administrador'))->exists());
    }

    public function test_it_can_run_twice_without_duplicating_accounts(): void
    {
        config(['seeding.staff_password' => 'Temporal#Equipo2026']);
        $this->seed(StaffUsersSeeder::class);

        config(['seeding.staff_password' => 'NuevaClave#Equipo2026']);
        $this->seed(StaffUsersSeeder::class);

        $seedEmails = collect(StaffUsersSeeder::accountGroups())->flatten(1)->pluck('email');

        $this->assertSame(18, User::whereIn('email', $seedEmails)->count());
        $this->assertTrue(Hash::check(
            'NuevaClave#Equipo2026',
            User::where('email', 'community.manager01@prodovidigital.com')->firstOrFail()->password
        ));
    }
}
