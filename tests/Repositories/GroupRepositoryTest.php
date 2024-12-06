<?php

namespace WalkerChiu\Group;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use WalkerChiu\Group\Models\Entities\Group;
use WalkerChiu\Group\Models\Entities\GroupLang;
use WalkerChiu\Group\Models\Repositories\GroupRepository;

class GroupRepositoryTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected $repository;

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

        $this->repository = $this->app->make(GroupRepository::class);
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
     * A basic functional test on GroupRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\Repository
     *
     * @return void
     */
    public function testGroupRepository()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-group.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-group.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-group.soft_delete', 1);

        $faker = \Faker\Factory::create();

        $user_id = $faker->uuid();
        DB::table(config('wk-core.table.user'))->insert([
            'id'       => $user_id,
            'name'     => $faker->username,
            'email'    => $faker->email,
            'password' => $faker->password
        ]);

        // Give
        $id_list = [];
        for ($i=1; $i<=3; $i++) {
            $record = $this->repository->save([
                'user_id'        => $user_id,
                'serial'         => $faker->isbn10,
                'identifier'     => $faker->slug,
                'order'          => $faker->randomNumber,
                'is_highlighted' => $faker->boolean,
                'is_enabled'     => $faker->boolean
            ]);
            array_push($id_list, $record->id);
        }

        // Get and Count records after creation
            // When
            $records = $this->repository->get();
            $count   = $this->repository->count();
            // Then
            $this->assertCount(3, $records);
            $this->assertEquals(3, $count);

        // Find someone
            // When
            $record = $this->repository->first();
            // Then
            $this->assertNotNull($record);

            // When
            $record = $this->repository->find($faker->uuid());
            // Then
            $this->assertNull($record);

        // Delete someone
            // When
            $this->repository->deleteByIds([$id_list[0]]);
            $count = $this->repository->count();
            // Then
            $this->assertEquals(2, $count);

            // When
            $this->repository->deleteByExceptIds([$id_list[2]]);
            $count = $this->repository->count();
            $record = $this->repository->find($id_list[2]);
            // Then
            $this->assertEquals(1, $count);
            $this->assertNotNull($record);

            // When
            $count = $this->repository->where('id', '>', 0)->count();
            // Then
            $this->assertEquals(1, $count);

            // When
            $count = $this->repository->whereWithTrashed('id', '>', 0)->count();
            // Then
            $this->assertEquals(3, $count);

            // When
            $count = $this->repository->whereOnlyTrashed('id', '>', 0)->count();
            // Then
            $this->assertEquals(2, $count);

        // Force delete someone
            // When
            $this->repository->forcedeleteByIds([$id_list[2]]);
            $records = $this->repository->get();
            // Then
            $this->assertCount(0, $records);

        // Restore records
            // When
            $this->repository->restoreByIds([$id_list[0], $id_list[1]]);
            $count = $this->repository->count();
            // Then
            $this->assertEquals(2, $count);
    }

    /**
     * Unit test about Lang creation on GroupRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Group\Models\Repositories\GroupRepository
     *
     * @return void
     */
    public function testcreateLangWithoutCheck()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-group.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-group.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-group.soft_delete', 1);

        // Give
        factory(Group::class)->create();

        // Find record
            // When
            $record = $this->repository->first();
            // Then
            $this->assertNotNull($record);

        // Create Lang
            // When
            $lang = $this->repository->createLangWithoutCheck(['morph_type' => get_class($record), 'morph_id' => $record->id, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
            // Then
            $this->assertInstanceOf(GroupLang::class, $lang);
    }

    /**
     * Unit test about Enable and Disable on GroupRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Group\Models\Repositories\GroupRepository
     *
     * @return void
     */
    public function testEnableAndDisable()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-group.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-group.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-group.soft_delete', 1);

        $faker = \Faker\Factory::create();

        // Give
        $db_morph_1 = factory(Group::class)->create(['is_enabled' => 1]);
        $db_morph_2 = factory(Group::class)->create();
        $db_morph_3 = factory(Group::class)->create();
        $db_morph_4 = factory(Group::class)->create();

        // Count records
            // When
            $count = $this->repository->count();
            $count_enabled = $this->repository->ofEnabled(null, null)->count();
            $count_disabled = $this->repository->ofDisabled(null, null)->count();
            // Then
            $this->assertEquals(4, $count);
            $this->assertEquals(1, $count_enabled);
            $this->assertEquals(3, $count_disabled);

        // Enable records
            // When
            $this->repository->whereToEnable(null, null, 'id', '=', $db_morph_4->id);
            $count_enabled = $this->repository->ofEnabled(null, null)->count();
            $count_disabled = $this->repository->ofDisabled(null, null)->count();
            // Then
            $this->assertEquals(2, $count_enabled);
            $this->assertEquals(2, $count_disabled);

        // Disable records
            // When
            $this->repository->whereToDisable(null, null, 'id', '>', 0);
            $count_enabled = $this->repository->ofEnabled(null, null)->count();
            $count_disabled = $this->repository->ofDisabled(null, null)->count();
            // Then
            $this->assertEquals(0, $count_enabled);
            $this->assertEquals(4, $count_disabled);
    }

    /**
     * Unit test about Query List on GroupRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Group\Models\Repositories\GroupRepository
     *
     * @return void
     */
    public function testQueryList()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-group.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-group.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-group.soft_delete', 1);

        $faker = \Faker\Factory::create();

        // Give
        $db_morph_1 = factory(Group::class)->create();
        $db_morph_2 = factory(Group::class)->create();
        $db_morph_3 = factory(Group::class)->create();
        $db_morph_4 = factory(Group::class)->create();

        // Get query
            // When
            sleep(1);
            $this->repository->find($db_morph_3->id)->touch();
            $records = $this->repository->ofNormal(null, null)->get();
            // Then
            $this->assertCount(4, $records);

            // When
            $record = $records->first();
            // Then
            $this->assertArrayNotHasKey('deleted_at', $record->toArray());
            $this->assertEquals($db_morph_3->id, $record->id);

        // Get query of trashed records
            // When
            $this->repository->deleteByIds([$db_morph_4->id]);
            $this->repository->deleteByIds([$db_morph_1->id]);
            $records = $this->repository->ofTrash(null, null)->get();
            // Then
            $this->assertCount(2, $records);

            // When
            $record = $records->first();
            // Then
            $this->assertArrayHasKey('deleted_at', $record);
            $this->assertEquals($db_morph_1->id, $record->id);
    }

    /**
     * Unit test about FormTrait on GroupRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Group\Models\Repositories\GroupRepository
     *     WalkerChiu\Core\Models\Forms\FormTrait
     *
     * @return void
     */
    public function testFormTrait()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-group.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-group.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-group.soft_delete', 1);

        $faker = \Faker\Factory::create();

        // Name
            // Give
            $db_morph_1 = factory(Group::class)->create();
            $db_morph_2 = factory(Group::class)->create();
            $db_lang_1 = $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Group::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
            $db_lang_2 = $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_2->id, 'morph_type' => Group::class, 'code' => 'zh_tw', 'key' => 'name', 'value' => '您好']);
            // When
            $result_1 = $this->repository->checkExistName(null, null, 'en_us', null, 'Hello');
            $result_2 = $this->repository->checkExistName(null, null, 'en_us', null, 'Hi');
            $result_3 = $this->repository->checkExistName(null, null, 'en_us', $db_morph_1->id, 'Hello');
            $result_4 = $this->repository->checkExistName(null, null, 'en_us', $db_morph_1->id, '您好');
            $result_5 = $this->repository->checkExistName(null, null, 'zh_tw', $db_morph_1->id, '您好');
            $result_6 = $this->repository->checkExistNameOfEnabled(null, null, 'en_us', null, 'Hello');
            // Then
            $this->assertTrue($result_1);
            $this->assertTrue(!$result_2);
            $this->assertTrue(!$result_3);
            $this->assertTrue(!$result_4);
            $this->assertTrue($result_5);
            $this->assertTrue(!$result_6);

        // Serial, Identifier
            // Give
            $db_morph_3 = factory(Group::class)->create(['serial' => '123', 'identifier' => 'A123']);
            $db_morph_4 = factory(Group::class)->create(['serial' => '124', 'identifier' => 'A124']);
            $db_morph_5 = factory(Group::class)->create(['serial' => '125', 'identifier' => 'A125', 'is_enabled' => 1]);
            // When
            $result_1 = $this->repository->checkExistSerial(null, null, null, '123');
            $result_2 = $this->repository->checkExistSerial(null, null, $db_morph_3->id, '123');
            $result_3 = $this->repository->checkExistSerial(null, null, $db_morph_3->id, '124');
            $result_4 = $this->repository->checkExistSerialOfEnabled(null, null, $db_morph_4->id, '124');
            $result_5 = $this->repository->checkExistSerialOfEnabled(null, null, $db_morph_4->id, '125');
            // Then
            $this->assertTrue($result_1);
            $this->assertTrue(!$result_2);
            $this->assertTrue($result_3);
            $this->assertTrue(!$result_4);
            $this->assertTrue($result_5);
            // When
            $result_1 = $this->repository->checkExistIdentifier(null, null, null, 'A123');
            $result_2 = $this->repository->checkExistIdentifier(null, null, $db_morph_3->id, 'A123');
            $result_3 = $this->repository->checkExistIdentifier(null, null, $db_morph_3->id, 'A124');
            $result_4 = $this->repository->checkExistIdentifierOfEnabled(null, null, $db_morph_4->id, 'A124');
            $result_5 = $this->repository->checkExistIdentifierOfEnabled(null, null, $db_morph_4->id, 'A125');
            // Then
            $this->assertTrue($result_1);
            $this->assertTrue(!$result_2);
            $this->assertTrue($result_3);
            $this->assertTrue(!$result_4);
            $this->assertTrue($result_5);
    }

    /**
     * Unit test about Auto Complete on GroupRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Group\Models\Repositories\GroupRepository
     *
     * @return void
     */
    public function testAutoComplete()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-group.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-group.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-group.soft_delete', 1);

        $faker = \Faker\Factory::create();

        // Give
        $db_morph_1 = factory(Group::class)->create(['serial' => 'A123', 'is_enabled' => 1]);
        $db_morph_2 = factory(Group::class)->create(['serial' => 'A124', 'is_enabled' => 1]);
        $db_lang_1 = $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Group::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
        $db_lang_1 = $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Group::class, 'code' => 'en_us', 'key' => 'description', 'value' => 'Good Morning!']);
        $db_lang_1 = $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Group::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello World']);
        $db_lang_1 = $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Group::class, 'code' => 'zh_tw', 'key' => 'name', 'value' => '您好']);
        $db_lang_1 = $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_1->id, 'morph_type' => Group::class, 'code' => 'zh_tw', 'key' => 'name', 'value' => '早安']);
        $db_lang_2 = $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_2->id, 'morph_type' => Group::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Bye']);

        // List array by name of enabled records
            // When
            $records = $this->repository->autoCompleteNameOfEnabled(null, null, 'en_us', 'H');
            // Then
            $this->assertCount(1, $records);

            // When
            $records = $this->repository->autoCompleteNameOfEnabled(null, null, 'zh_tw', 'H');
            // Then
            $this->assertCount(0, $records);

        // List array by serial of enabled records
            // When
            $records = $this->repository->autoCompleteSerialOfEnabled(null, null, 'en_us', 'A');
            // Then
            $this->assertCount(2, $records);
    }

    /**
     * Unit test about List on GroupRepository.
     *
     * For WalkerChiu\Core\Models\Repositories\RepositoryHasmorphTrait
     *     WalkerChiu\Group\Models\Repositories\GroupRepository
     *
     * @return void
     */
    public function testList()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-group.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-group.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-group.soft_delete', 1);
        Config::set('wk-group.output_format', 'array');

        $faker = \Faker\Factory::create();

        // When
        $records = $this->repository->list(null, null, 'en_us', [], true, null, null, true);
        // Then
        $this->assertTrue(is_array($records));
        $this->assertTrue(empty($records));

        // Give
        $db_morph_1  = factory(Group::class)->create();
        $db_morph_2  = factory(Group::class)->create();
        $db_morph_3  = factory(Group::class)->create(['is_enabled' => 1]);
        $db_morph_4  = factory(Group::class)->create(['is_enabled' => 1]);
        $db_morph_5  = factory(Group::class)->create(['is_enabled' => 1]);
        $db_morph_6  = factory(Group::class)->create(['is_enabled' => 1]);
        $db_morph_7  = factory(Group::class)->create(['is_enabled' => 1]);
        $db_morph_8  = factory(Group::class)->create(['is_enabled' => 1]);
        $db_morph_9  = factory(Group::class)->create(['is_enabled' => 1]);
        $db_morph_10 = factory(Group::class)->create();

        // Give
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_3->id, 'morph_type' => Group::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_3->id, 'morph_type' => Group::class, 'code' => 'en_us', 'key' => 'description', 'value' => 'Good Morning!']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_3->id, 'morph_type' => Group::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hello World']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_3->id, 'morph_type' => Group::class, 'code' => 'zh_tw', 'key' => 'name', 'value' => '您好']);
        $this->repository->createLangWithoutCheck(['morph_id' => $db_morph_4->id, 'morph_type' => Group::class, 'code' => 'en_us', 'key' => 'name', 'value' => 'Hi']);

        // When
        $records = $this->repository->list(null, null, 'en_us', []);
        // Then
        $this->assertEquals(2, $records->count());

        // When
        $records_1 = $this->repository->list(null, null, 'en_us', ['name' => 'H']);
        $records_2 = $this->repository->list(null, null, 'en_us', ['name' => 'H', 'description' => 'G']);
        $records_3 = $this->repository->list(null, null, 'en_us', ['name' => 'Hi']);
        // Then
        $this->assertEquals(2, $records_1->count());
        $this->assertEquals(1, $records_2->count());
        $this->assertEquals(1, $records_3->count());
    }
}
