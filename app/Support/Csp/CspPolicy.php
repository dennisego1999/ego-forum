<?php

namespace App\Support\Csp;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Policy;
use Spatie\Csp\Scheme;

class CspPolicy extends Policy
{
    public function configure(): void
    {
        $this
            ->addDirective(Directive::BASE, Keyword::SELF)
            ->addDirective(Directive::CONNECT, Keyword::SELF)
            ->addDirective(Directive::DEFAULT, Keyword::SELF)
            ->addDirective(Directive::FORM_ACTION, Keyword::SELF)
            ->addDirective(Directive::IMG, $this->getImage())
            ->addDirective(Directive::MEDIA, Keyword::SELF)
            ->addDirective(Directive::OBJECT, Keyword::NONE)
            ->addDirective(Directive::CONNECT, [
                config('broadcasting.connections.pusher.options.host'),
                'wss://'.config('broadcasting.connections.pusher.options.host'),
            ])
            ->addDirective(Directive::SCRIPT, [
                Keyword::SELF,
                'fonts.bunny.net',
                'unsafe-eval',
                'blob:',
            ])
            ->addDirective(Directive::STYLE, [
                Keyword::SELF,
                'cdnjs.cloudflare.com',
                'fonts.bunny.net',
                'unsafe-inline',
            ])
            ->addDirective(Directive::FONT, [
                Keyword::SELF,
                'fonts.bunny.net',
                'cdnjs.cloudflare.com',
            ])
            ->addNonceForDirective(Directive::SCRIPT);

        $this->addDirective(Directive::CONNECT, Scheme::WSS);

        // Allow vite specific things in development
        if (app()->environment() === 'local' && file_exists(public_path('hot'))) {
            $this->addDirective(Directive::CONNECT, [Scheme::WSS, '*']);
        }
    }

    private function getImage(): array
    {
        $data = [
            Keyword::SELF,
            Scheme::DATA,
            'gravatar.com',
        ];

        if (! App::runningUnitTests() && config('filesystems.default') === 's3') {
            $data[] = Storage::disk('s3')->url('');
        }

        return $data;
    }
}
