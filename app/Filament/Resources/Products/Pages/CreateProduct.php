<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateProduct extends CreateRecord
{
    protected function handleRecordCreation(array $data): Model
    {
        if (!isset($data['company_id'])) {
            $data['company_id'] = Auth::user()->id;
        }

        return parent::handleRecordCreation($data);
    }

    protected static string $resource = ProductResource::class;
}
