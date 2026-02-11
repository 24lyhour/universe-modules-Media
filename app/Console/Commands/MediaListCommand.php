<?php

namespace Modules\Media\Console\Commands;

use App\Services\MediaService;
use Illuminate\Console\Command;

class MediaListCommand extends Command
{
    protected $signature = 'media:list
                            {--collection= : Filter by collection name}
                            {--type= : Filter by mime type (image, video, document)}
                            {--limit=20 : Limit results}';

    protected $description = 'List all media files in the system';

    public function handle(MediaService $mediaService): int
    {
        $this->info('Media Library');
        $this->line('-------------');

        $filters = [];

        if ($collection = $this->option('collection')) {
            $filters['collection_name'] = $collection;
        }

        if ($type = $this->option('type')) {
            $mimeTypes = [
                'image' => 'image/',
                'video' => 'video/',
                'audio' => 'audio/',
                'document' => 'application/',
            ];
            if (isset($mimeTypes[$type])) {
                $filters['mime_type'] = $mimeTypes[$type];
            }
        }

        $media = $mediaService->all($filters)->take((int) $this->option('limit'));

        if ($media->isEmpty()) {
            $this->warn('No media found.');

            return Command::SUCCESS;
        }

        $rows = $media->map(function ($item) use ($mediaService) {
            return [
                $item->id,
                $item->name,
                $item->collection_name,
                $item->mime_type,
                $mediaService->formatBytes($item->size),
                $item->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Collection', 'Type', 'Size', 'Created'],
            $rows
        );

        $this->newLine();
        $this->info("Showing {$media->count()} of {$mediaService->getTotalCount()} total media files.");

        return Command::SUCCESS;
    }
}
