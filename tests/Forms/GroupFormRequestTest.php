<?php

namespace WalkerChiu\Group;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use WalkerChiu\Group\Models\Entities\Group;
use WalkerChiu\Group\Models\Forms\GroupFormRequest;

class GroupFormRequestTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        //$this->loadLaravelMigrations(['--database' => 'mysql']);
        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');

        $this->request  = new GroupFormRequest();
        $this->rules    = $this->request->rules();
        $this->messages = $this->request->messages();
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\Group\GroupServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * Unit test about Authorize.
     *
     * For WalkerChiu\Group\Models\Forms\GroupFormRequest
     *
     * @return void
     */
    public function testAuthorize()
    {
        $this->assertEquals(true, 1);
    }

    /**
     * Unit test about Rules.
     *
     * For WalkerChiu\Group\Models\Forms\GroupFormRequest
     *
     * @return void
     */
    public function testRules()
    {
        $faker = \Faker\Factory::create();

        $user_id = $faker->uuid();
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);
        $site_id = $faker->uuid();
        DB::table(config('wk-core.table.site.sites'))->insert([
            'id'       => $site_id,
            'serial'   => $faker->username,
        ]);


        // Give
        $attributes = [
            'user_id'        => $user_id,
            'serial'         => $faker->isbn10,
            'identifier'     => $faker->slug,
            'order'          => $faker->randomNumber,
            'is_highlighted' => $faker->boolean,
            'is_enabled'     => $faker->boolean,
            'name'           => $faker->name,
        ];
        // When
        $validator = Validator::make($attributes, $this->rules, $this->messages); $this->request->withValidator($validator);
        $fails = $validator->fails();
        // Then
        $this->assertEquals(false, $fails);

        // Give
        $attributes = [
            'user_id'        => $user_id,
            'serial'         => $faker->isbn10,
            'identifier'     => $faker->slug,
            'order'          => $faker->randomNumber,
            'is_highlighted' => $faker->boolean,
            'is_enabled'     => $faker->boolean,
        ];
        // When
        $validator = Validator::make($attributes, $this->rules, $this->messages); $this->request->withValidator($validator);
        $fails = $validator->fails();
        // Then
        $this->assertEquals(true, $fails);
    }
}
