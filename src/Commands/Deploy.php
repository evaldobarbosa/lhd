<?php

namespace LHD\Commands;

use Illuminate\Console\Command;
use \mikehaertl\shellcommand\Command as ShellCommand;

class Deploy extends Command
{
    private $stopped = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heroku:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read <root>/app.json and deploy the app';

    public function handle()
    {
        $json = json_decode(
            file_get_contents(
                base_path('app.json')
            ),
            true
        );

        try {
            $this->runIt('create', $json['name']);

            foreach ($json['env'] as $key => $env) {
                $this->configIt($key, $env['value'], $json['name']);
            }

            if (isset($json['addons'][0])) {
                $this->runIt('addons:create', $json['addons'][0]);
            }

            if (isset($json['scripts']['postdeploy'])) {
                $this->runIt('run', $json['scripts']['postdeploy']);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function runIt($command, $value)
    {
        if ($this->stopped) {
            $this->error('Something bad hapenned');
        }
        $commandStr = sprintf(
            'heroku %s %s',
            $command,
            $value
        );

        echo ">>> Executing: {$commandStr}\n";
        // exec($command);
        $command = new ShellCommand($commandStr);
        if ($command->execute()) {
            $this->info($command->getOutput());
        } else {
            if (strpos($command->getError(), 'already taken') !== false) {
                throw new \Exception(
                    "This application already taken by you or another user"
                );
            }
            $exitCode = $command->getExitCode();
        }
    }

    private function configIt($key, $value, $appName)
    {
        $value = sprintf(
            '%s=%s -a %s',
            $key,
            $value,
            $appName
        );

        $this->runIt('config:set', $value);
    }
}
