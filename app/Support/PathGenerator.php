<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class PathGenerator extends DefaultPathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        $prefix = "$media->model_type/$media->collection_name";
        $yearAndMonth = $media->created_at->format('Y/m');

        return "$prefix/$yearAndMonth/$media->id/";
    }
}
