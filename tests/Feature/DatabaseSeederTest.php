<?php

namespace Tests\Feature;

use App\Models\Troop;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_environment_only_seeds_admin_account(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'is_admin' => true,
        ]);

        $this->assertSame(1, User::count());
        $this->assertSame(0, Troop::count());
    }

    public function test_demo_seeder_skips_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--class' => DemoSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertSame(0, Troop::count());
    }

    public function test_local_environment_seeds_admin_and_sample_data(): void
    {
        Storage::fake('local');
        $this->app['env'] = 'local';

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'is_admin' => true,
        ]);

        $this->assertSame(40, Troop::count());
    }
}
