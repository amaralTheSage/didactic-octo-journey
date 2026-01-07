<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Request;

class PixQrCode extends Page
{
    protected string $view = 'filament.pages.pix-qr-code';

    public string $qrBase64;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    public static function canAccess(): bool
    {
        return Gate::allows('is_company');
    }


    public function mount(Request $request): void
    {

        $this->qrBase64 = $request['qrcode_base64']['data']['brCodeBase64'];
    }
}
