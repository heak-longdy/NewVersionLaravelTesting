<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

use function Illuminate\Support\php_binary;

#[AsCommand(name: 'serve', description: 'Serve the application on the PHP development server with extended upload limits')]
class ServeCommand extends BaseServeCommand
{
    /**
     * Get the full server command with 100MB upload limits.
     *
     * @return array
     */
    protected function serverCommand()
    {
        $server = file_exists(base_path('server.php'))
            ? base_path('server.php')
            : base_path('vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php');

        return [
            php_binary(),
            '-d',
            'upload_max_filesize=100M',
            '-d',
            'post_max_size=100M',
            '-d',
            'memory_limit=256M',
            '-S',
            $this->host().':'.$this->port(),
            $server,
        ];
    }
}
