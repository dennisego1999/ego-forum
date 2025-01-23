<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class LanguageLine extends \ArtcoreSociety\TranslationImport\Models\LanguageLine
{
    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function text(): Attribute
    {
        // Retrieve the text and cast from JSON as in the parent model
        return Attribute::get(function ($text) {
            $text = $this->castAttribute('text', $text) ?: [];

            // Replace variables while reading only
            if (! $this->importing) {
                foreach ($text as &$translation) {
                    $translation = Str::replaceVariables($translation);
                }
            }

            return $text;
        });
    }
}
