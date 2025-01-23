<?php

namespace App\Models;

use App\Contracts\HasUserPermissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    public static array $defaultModelPermissions = [
        'view_any',
        'view',
        'create',
        'update',
        'delete',
        'restore',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function getLabel(): string
    {
        return Str::of($this->name)
            ->headline()
            ->lower();
    }

    public static function for(string $prefix, string|Model $model): string
    {
        // Cast model back to string class reference
        if ($model instanceof Model) {
            $model = $model::class;
        }

        $model = Str::of($model)
            ->classBasename()
            ->headline()
            ->lower()
            ->plural()
            ->snake()
            ->toString();

        return "{$prefix}_$model";
    }

    public static function getModels(): array
    {
        $files = File::allFiles(app_path('Models'));

        return collect($files)
            // Convert namespaces
            ->map(fn ($item) => Str::of($item->getPathname())
                ->after('app/')
                ->before('.php')
                ->prepend('/App/')
                ->replace('/', '\\')
                ->toString()
            )
            ->filter(function ($class) {
                // Bail if the class does not exist
                if (! class_exists($class)) {
                    return false;
                }

                $reflection = new ReflectionClass($class);

                // Only include actual ELoquent models
                if (! $reflection->isSubclassOf(Model::class) || $reflection->isAbstract()) {
                    return false;
                }

                // Only include models with our custom contract
                return $reflection->implementsInterface(HasUserPermissions::class);
            })
            ->values()
            ->all();
    }

    public static function getModelHeadline(string $model): string
    {
        return Str::of($model)
            ->afterLast('\\')
            ->headline()
            ->plural()
            ->plural();
    }

    public static function getModelName(string $model): string
    {
        return Str::of($model)
            ->afterLast('\\')
            ->headline()
            ->plural()
            ->plural()
            ->snake();
    }

    public static function getModelPermissions(Model $model): array
    {
        // Get the filtered set of permissions when defined
        if (property_exists($model, 'permissionPrefixes')) {
            return $model::$permissionPrefixes;
        }

        // Use the default set of permissions
        return self::$defaultModelPermissions;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
}
