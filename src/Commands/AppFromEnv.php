<?php

namespace LHD\Commands;

use Illuminate\Console\Command;
use mikehaertl\shellcommand\Command as ShellCommand;

class AppFromEnv extends Command
{
    private $envData = [];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heroku:app-from-env';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read .env.production contents create app.json';

    public function handle()
    {
        if (!file_exists(base_path('.env.production'))) {
            throw new \Exception('File .env.production not exists');
        }

        $this->getEnvData();
        $this->makeProcFile();
        
        $appData = (file_exists('app.json'))
            ? json_decode(file_get_contents('app.json'), true)
            : ["image" => 'heroku/php', "addons" => []];

        $appData['name'] = $this->getAppName();

        if (!isset($appData['env'])) {
            $appData['env'] = [];
        }
        
        foreach ($this->envData as $key => $value) {
            $appData['env'][$key] = (object)['value' => $value];
        }

        file_put_contents(base_path('app.json'), json_encode($appData));
    }

    private function makeProcFile()
    {
        if (!file_exists(base_path('Procfile'))) {
            file_put_contents(
                base_path('Procfile'),
                'web: vendor/bin/heroku-php-apache2 public/'
            );
        }
    }

    private function getAppName()
    {
        if (!isset($this->envData['APP_NAME'])) {
            return 'my-lhd-app';
        }

        return strtolower($this->envData['APP_NAME']);
    }

    private function getEnvData()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(
            base_path(),
            '.env.production'
        );

        $this->envData = $dotenv->safeLoad();
    }
}
