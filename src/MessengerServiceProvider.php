<?php

namespace Lexx\Messenger;

use Lexx\Messenger\Models\Message;
use Lexx\Messenger\Models\Models;
use Lexx\Messenger\Models\Participant;
use Lexx\Messenger\Models\Thread;
use Illuminate\Support\ServiceProvider;

class MessengerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // add extra routes to our app
        // include __DIR__.'/routes.php'; // leave it to others for customization

        $this->offerPublishing();
        $this->setMessengerModels();
        $this->setUserModel();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
    }

    /**
     * Setup the configuration for Messenger.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            base_path('vendor/lexxyungcarter/messenger/config/config.php'),
            'messenger'
        );
    }

    /**
     * Setup the resource publishing groups for Messenger.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                base_path('vendor/lexxyungcarter/messenger/config/config.php') => config_path('messenger.php'),
            ], 'config');

            $this->publishes([
                base_path('vendor/lexxyungcarter/messenger/migrations') => base_path('database/migrations'),
            ], 'migrations');
        }
    }

    protected function setMessengerModels()
    {
        $config = $this->app->make('config');

        Models::setMessageModel($config->get('messenger.message_model', Message::class));
        Models::setThreadModel($config->get('messenger.thread_model', Thread::class));
        Models::setParticipantModel($config->get('messenger.participant_model', Participant::class));

        Models::setTables([
            'messages' => $config->get('messenger.messages_table', Models::message()->getTable()),
            'participants' => $config->get('messenger.participants_table', Models::participant()->getTable()),
            'threads' => $config->get('messenger.threads_table', Models::thread()->getTable()),
        ]);
    }

    protected function setUserModel()
    {
        $config = $this->app->make('config');

        $model = $config->get('auth.providers.users.model', function () use ($config) {
            return $config->get('auth.model', $config->get('messenger.user_model'));
        });

        Models::setUserModel($model);

        Models::setTables([
            'users' => (new $model)->getTable(),
        ]);
    }
}
