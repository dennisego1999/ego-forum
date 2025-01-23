<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class Role extends \Spatie\Permission\Models\Role
{
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $appends = [
        'label',
    ];

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function label(): Attribute
    {
        return Attribute::get(function () {
            // Use the system translation when available
            if (Lang::has("models.roles.labels.$this->name")) {
                return trans("models.roles.labels.$this->name");
            }

            // Use name by default
            return $this->name;
        });
    }

    public function getEmail(string $baseEmail): string
    {
        $email = Str::of($baseEmail);

        // Split the email around the "at sign" (@)
        $before = $email->before('@');
        $after = $email->after('@');

        // Suffix the email by the role
        if ($this->name !== RoleEnum::SUPER_ADMIN->value) {
            return "$before+$this->name@$after";
        }

        return "$before@$after";
    }
}
