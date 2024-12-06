<?php

namespace WalkerChiu\Group;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\Group\Models\Entities\Group;
use WalkerChiu\Group\Models\Entities\GroupLang;

class GroupTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
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
     * A basic functional test on Group.
     *
     * For WalkerChiu\Group\Models\Entities\Group
     *
     * @return void
     */
    public function testGroup()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-group.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-group.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-group.soft_delete', 1);

        // Give
        $db_morph_1 = factory(Group::class)->create();
        $db_morph_2 = factory(Group::class)->create();
        $db_morph_3 = factory(Group::class)->create(['is_enabled' => 1]);

        // Get records after creation
            // When
            $records = Group::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $db_morph_2->delete();
            $records = Group::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Group::withTrashed()
                 ->find($db_morph_2->id)
                 ->restore();
            $record_2 = Group::find($db_morph_2->id);
            $records = Group::all();
            // Then
            $this->assertNotNull($record_2);
            $this->assertCount(3, $records);

        // Return Lang class
            // When
            $class = $record_2->lang();
            // Then
            $this->assertEquals($class, GroupLang::class);

        // Scope query on enabled records
            // When
            $records = Group::ofEnabled(null, null)
                            ->get();
            // Then
            $this->assertCount(1, $records);

        // Scope query on disabled records
            // When
            $records = Group::ofDisabled(null, null)
                            ->get();
            // Then
            $this->assertCount(2, $records);
    }
}
