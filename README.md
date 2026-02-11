# Media Module

A global media library module for the Universe project, powered by [Spatie Laravel Media Library](https://spatie.be/docs/laravel-medialibrary).

## Installation

The module is automatically loaded. Run the migration:

```bash
php artisan migrate
```

## Usage

### Using the Trait in Models

Add the `HasMediaLibrary` trait to any model that needs media support:

```php
<?php

namespace Modules\Product\Models;

use App\Traits\HasMediaLibrary;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class Product extends Model implements HasMedia
{
    use HasMediaLibrary;

    // Define media collections (optional)
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public');

        $this->addMediaCollection('documents')
            ->useDisk('public');
    }
}
```

### Uploading Media

```php
// Upload a single file
$product->uploadMedia($request->file('image'), 'images');

// Upload multiple files
$product->uploadMultipleMedia($request->file('images'), 'images');

// Replace media in a collection
$product->replaceMedia($request->file('avatar'), 'avatars');
```

### Retrieving Media

```php
// Get URL of first media
$url = $product->getMediaUrl('images');

// Get all media URLs
$urls = $product->getMediaUrls('images');

// Check if has media
if ($product->hasMediaInCollection('images')) {
    // ...
}

// Get media count
$count = $product->getMediaCount('images');
```

### Using MediaService

For more control, use the `MediaService` directly:

```php
use App\Services\MediaService;

class ProductController extends Controller
{
    public function __construct(private MediaService $mediaService)
    {
    }

    public function upload(Request $request, Product $product)
    {
        $media = $this->mediaService->upload(
            $product,
            $request->file('image'),
            'images',
            'public',
            ['alt' => 'Product image']
        );

        return response()->json(['url' => $media->getUrl()]);
    }

    public function stats()
    {
        return $this->mediaService->getStats();
    }
}
```

## CLI Commands

### List Media

```bash
# List all media
php artisan media:list

# Filter by collection
php artisan media:list --collection=avatars

# Filter by type (image, video, audio, document)
php artisan media:list --type=image

# Limit results
php artisan media:list --limit=50
```

### View Statistics

```bash
php artisan media:stats
```

### Clean Orphaned Files

```bash
# Dry run
php artisan media:clean --dry-run

# Force cleanup
php artisan media:clean --force
```

## Configuration

The media library configuration is located at `config/media-library.php`.

### Common Configuration Options

```php
// config/media-library.php

return [
    // Default disk
    'disk_name' => 'public',

    // Max file size (in bytes)
    'max_file_size' => 1024 * 1024 * 10, // 10MB

    // Image driver (gd or imagick)
    'image_driver' => 'gd',

    // Generate responsive images
    'generate_responsive_images' => true,
];
```

## Directory Structure

```
Modules/Media/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── MediaCleanCommand.php
│   │       ├── MediaListCommand.php
│   │       └── MediaStatsCommand.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── MediaController.php
│   └── Providers/
│       ├── EventServiceProvider.php
│       ├── MediaServiceProvider.php
│       └── RouteServiceProvider.php
├── config/
│   └── config.php
├── database/
│   └── seeders/
├── resources/
│   └── views/
├── routes/
│   ├── api.php
│   └── web.php
└── README.md
```

## Global Services

| File | Description |
|------|-------------|
| `app/Services/MediaService.php` | Service class for media operations |
| `app/Traits/HasMediaLibrary.php` | Trait for models with media support |

## License

Proprietary
