<?php

namespace Modules\Media\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Commands\CleanCommand;

class MediaCleanCommand extends Command
{
    protected $signature = 'media:clean
                            {--dry-run : List files that would be removed without actually removing them}
                            {--force : Force the operation without confirmation}';

    protected $description = 'Clean orphaned media files and directories';

    public function handle(): int
    {
        $this->info('Media Library Cleanup');
        $this->line('---------------------');

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN - No files will be deleted.');
            $this->newLine();
        }

        if (! $this->option('force') && ! $this->option('dry-run')) {
            if (! $this->confirm('This will remove orphaned media files. Continue?')) {
                $this->info('Operation cancelled.');

                return Command::SUCCESS;
            }
        }

        $arguments = [];

        if ($this->option('dry-run')) {
            $arguments['--dry-run'] = true;
        }

        $this->call(CleanCommand::class, $arguments);

        $this->newLine();
        $this->info('Cleanup completed.');

        return Command::SUCCESS;
    }
}
