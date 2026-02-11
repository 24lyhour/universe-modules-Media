<?php

namespace Modules\Media\Console\Commands;

use App\Services\MediaService;
use Illuminate\Console\Command;

class MediaStatsCommand extends Command
{
    protected $signature = 'media:stats';

    protected $description = 'Display media library statistics';

    public function handle(MediaService $mediaService): int
    {
        $this->info('Media Library Statistics');
        $this->line('------------------------');

        $stats = $mediaService->getStats();

        $this->newLine();
        $this->info('Overview');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Files', number_format($stats['total_count'])],
                ['Total Size', $stats['total_size_formatted']],
            ]
        );

        if (! empty($stats['by_collection'])) {
            $this->newLine();
            $this->info('By Collection');
            $collectionRows = collect($stats['by_collection'])->map(function ($item) use ($mediaService) {
                return [
                    $item['collection_name'],
                    number_format($item['count']),
                    $mediaService->formatBytes($item['total_size']),
                ];
            })->toArray();

            $this->table(
                ['Collection', 'Count', 'Size'],
                $collectionRows
            );
        }

        if (! empty($stats['by_mime_type'])) {
            $this->newLine();
            $this->info('By File Type');
            $typeRows = collect($stats['by_mime_type'])->map(function ($item) {
                return [
                    $item['mime_type'],
                    number_format($item['count']),
                ];
            })->toArray();

            $this->table(
                ['MIME Type', 'Count'],
                $typeRows
            );
        }

        return Command::SUCCESS;
    }
}
