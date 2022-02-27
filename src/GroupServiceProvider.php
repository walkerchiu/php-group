<?php

namespace WalkerChiu\Group;

use Illuminate\Support\ServiceProvider;

class GroupServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/group.php' => config_path('wk-group.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_group_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_group_table.php',
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-group');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-group'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-group.command.cleaner')
            ]);
        }

        config('wk-core.class.group.group')::observe(config('wk-core.class.group.groupObserver'));
        config('wk-core.class.group.groupLang')::observe(config('wk-core.class.group.groupLangObserver'));
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-group')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/group.php', 'wk-group'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/group.php', 'group'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
