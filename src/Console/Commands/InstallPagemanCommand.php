<?php

namespace Tsrgtm\Pageman\Console\Commands; // Replace YourVendorName

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User; // Assuming the User model is in the default App\Models namespace

class InstallPagemanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pageman:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Pageman CMS and setup basic configurations.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Welcome to Pageman CMS Installation!');

        // 1. Set App Name
        if (!$this->setAppName()) {
            return Command::FAILURE;
        }

        // 2. Configure Database (MySQL only for now)
        if (!$this->configureDatabase()) {
            return Command::FAILURE;
        }

        // 3. Run Migrations (including adding role to users table)
        if (!$this->runMigrationsAndRoleSetup()) {
            return Command::FAILURE;
        }

        // 4. Create Admin User
        if (!$this->createAdminUser()) {
            return Command::FAILURE;
        }

        $this->info('Pageman CMS installed successfully!');
        $this->comment('Please make sure your web server is configured to point to the public directory.');
        $this->comment('You might need to clear your config cache: php artisan config:cache');

        return Command::SUCCESS;
    }

    /**
     * Set the application name in the .env file.
     */
    protected function setAppName(): bool
    {
        $this->line('Setting up Application Name...');
        $appName = $this->ask('Enter your Application Name', env('APP_NAME', 'Pageman'));
        if (!$this->setEnvVariable('APP_NAME', $appName)) {
            $this->error('Failed to set APP_NAME in .env file.');
            return false;
        }
        $this->info("APP_NAME set to '{$appName}'.");
        return true;
    }

    /**
     * Configure MySQL database connection in the .env file.
     */
    protected function configureDatabase(): bool
    {
        $this->line('Configuring Database Connection (MySQL)...');

        if ($this->confirm('Do you want to configure the database connection now?', true)) {
            $dbHost = $this->ask('Database Host', env('DB_HOST', '127.0.0.1'));
            $dbPort = $this->ask('Database Port', env('DB_PORT', '3306'));
            $dbDatabase = $this->ask('Database Name', env('DB_DATABASE', 'pageman'));
            $dbUsername = $this->ask('Database Username', env('DB_USERNAME', 'root'));
            $dbPassword = $this->secret('Database Password'); // Use secret for passwords

            if (!$this->setEnvVariable('DB_CONNECTION', 'mysql'))
                return false;
            if (!$this->setEnvVariable('DB_HOST', $dbHost))
                return false;
            if (!$this->setEnvVariable('DB_PORT', $dbPort))
                return false;
            if (!$this->setEnvVariable('DB_DATABASE', $dbDatabase))
                return false;
            if (!$this->setEnvVariable('DB_USERNAME', $dbUsername))
                return false;
            if (!$this->setEnvVariable('DB_PASSWORD', $dbPassword ?? ''))
                return false; // Handle null password

            $this->info('Database connection details updated in .env file.');

            // Test DB Connection
            $this->info('Attempting to connect to the database...');
            // Temporarily update config for current process to test connection
            config([
                'database.connections.mysql_test_pageman' => [
                    'driver' => 'mysql',
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'database' => $dbDatabase,
                    'username' => $dbUsername,
                    'password' => $dbPassword,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ],
            ]);

            try {
                DB::connection('mysql_test_pageman')->getPdo();
                $this->info('Database connection successful!');
            } catch (\Exception $e) {
                $this->error('Database connection failed: ' . $e->getMessage());
                $this->warn('Please check your .env file and database server.');
                if (!$this->confirm('Continue installation despite database connection failure?', false)) {
                    return false;
                }
            }
        } else {
            $this->info('Skipping database configuration. Please ensure it is set up manually in your .env file.');
        }
        return true;
    }

    /**
     * Run migrations and ensure the role column exists on the users table.
     */
    protected function runMigrationsAndRoleSetup(): bool
    {
        $this->line('Running database migrations...');

        // Check if 'role' column exists in users table
        if (!Schema::hasColumn('users', 'role')) {
            $this->warn("The 'users' table does not have a 'role' column.");
            if ($this->confirm("Do you want Pageman to attempt to publish and run a migration to add the 'role' column?", true)) {
                // Path to the migration file within your package
                $migrationSourcePath = __DIR__ . '/../../../database/migrations/add_role_to_users_table.php.stub'; // Adjust path as needed
                $migrationFileName = date('Y_m_d_His') . '_add_role_to_users_table.php';
                $migrationTargetPath = database_path('migrations/' . $migrationFileName);

                if (!File::exists(database_path('migrations'))) {
                    File::makeDirectory(database_path('migrations'), 0755, true);
                }

                if (File::exists($migrationSourcePath)) {
                    File::copy($migrationSourcePath, $migrationTargetPath);
                    $this->info("Migration to add 'role' column published: {$migrationFileName}");
                } else {
                    $this->error("Role migration stub not found at: {$migrationSourcePath}");
                    $this->comment("Please manually add a 'role' column (e.g., VARCHAR or ENUM) to your 'users' table.");
                    $this->comment("Example migration snippet:");
                    $this->comment("\Schema::table('users', function (Blueprint \$table) { \$table->string('role')->default('user')->after('email'); });"); // Example
                    if (!$this->confirm('Continue without the role column being automatically added?', false)) {
                        return false;
                    }
                }
            } else {
                $this->comment("Skipping automatic role column addition. Ensure the 'users' table has a 'role' column for Pageman to function correctly.");
            }
        }

        // Run all migrations
        try {
            Artisan::call('migrate', ['--force' => true]); // --force for production
            $this->info(Artisan::output());
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return false;
        }
        $this->info('Migrations completed.');
        return true;
    }


    /**
     * Create an admin user.
     */
    protected function createAdminUser(): bool
    {
        $this->line('Creating Admin User...');
        if ($this->confirm('Do you want to create an admin user now?', true)) {
            $name = $this->ask('Admin Name', 'Admin');
            $email = $this->ask('Admin Email', 'admin@pageman.test');
            $password = $this->secret('Admin Password (min 8 characters)');

            while (strlen($password) < 8) {
                $this->error('Password must be at least 8 characters.');
                $password = $this->secret('Admin Password (min 8 characters)');
            }

            $adminRole = config('pageman.admin_role_name', 'admin'); // Get admin role name from config

            try {
                if (!class_exists(User::class)) {
                    $this->error('User model (App\Models\User) not found. Cannot create admin user.');
                    $this->comment('Please ensure you have a User model at App\Models\User or adjust the namespace in this command.');
                    return false;
                }

                // Check if user with this email already exists
                if (User::where('email', $email)->exists()) {
                    $this->warn("A user with the email '{$email}' already exists.");
                    if ($this->confirm("Update this user to be an admin (if not already)?", true)) {
                        $this->info("Updating user to be an admin.");
                    } else {
                        $this->info("Skipping admin user creation/update.");
                        return true;
                    }
                    $user = User::where('email', $email)->first();
                    $user->password = Hash::make($password); // Update password
                } else {
                    $user = new User();
                    $user->email = $email;
                    $user->password = Hash::make($password);
                }

                $user->name = $name;

                // Set the role - ensure the 'role' attribute is fillable or handled by the model
                if (Schema::hasColumn('users', 'role')) {
                    $user->role = $adminRole;
                } else {
                    $this->warn("Cannot set role for admin user as 'role' column does not exist in users table.");
                }

                $user->save();

                $this->info("Admin user '{$email}' created/updated successfully with role '{$adminRole}'.");
                $this->publishUserTrait();


            } catch (\Exception $e) {
                $this->error('Failed to create admin user: ' . $e->getMessage());
                return false;
            }
        } else {
            $this->info('Skipping admin user creation.');
        }
        return true;
    }

    /**
     * Publish the HasPagemanUserRoles trait.
     */
    protected function publishUserTrait()
    {
        $traitSourcePath = __DIR__ . '/../../../src/Traits/HasPagemanUserRoles.php.stub'; // Adjust path
        $traitTargetPath = app_path('Traits/Pageman/HasPagemanUserRoles.php'); // Target in app/Traits/Pageman

        if (!File::exists(app_path('Traits/Pageman'))) {
            File::makeDirectory(app_path('Traits/Pageman'), 0755, true, true);
        }

        if (File::exists($traitSourcePath)) {
            File::copy($traitSourcePath, $traitTargetPath);
            $this->info("Trait 'HasPagemanUserRoles.php' published to 'app/Traits/Pageman/'.");
            $this->comment("Please add 'use App\\Traits\\Pageman\\HasPagemanUserRoles;' and use the trait in your App\\Models\\User model.");
        } else {
            $this->error("User role trait stub not found at: {$traitSourcePath}");
        }
    }


    /**
     * Helper function to set an environment variable.
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    protected function setEnvVariable(string $key, string $value): bool
    {
        $envFilePath = $this->laravel->environmentFilePath(); // Gets base_path('.env')

        // Ensure value is suitable for .env (e.g., wrap strings with spaces in quotes)
        if (Str::contains($value, ' ') || Str::contains($value, '#')) {
            $value = '"' . $value . '"';
        }

        $oldValue = env($key);
        $oldLine = $key . '=' . (Str::contains($oldValue, ' ') || Str::contains($oldValue, '#') ? '"' . $oldValue . '"' : $oldValue);
        $newLine = $key . '=' . $value;

        $contents = File::get($envFilePath);

        if (preg_match("/^{$key}=.*/m", $contents)) {
            $contents = preg_replace("/^{$key}=.*/m", $newLine, $contents);
        } else {
            $contents .= "\n" . $newLine;
        }

        try {
            File::put($envFilePath, $contents);
            // Also update current process's env
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            return true;
        } catch (\Exception $e) {
            $this->error("Error writing to .env file: " . $e->getMessage());
            return false;
        }
    }
}
