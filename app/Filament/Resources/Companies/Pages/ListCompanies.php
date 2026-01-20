<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Companies\CompanyResource;
use App\Models\CompanyInfo;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    public function getTabs(): array
    {
        $user = Auth::user();
        $tabs = [];

        if ($user->role === UserRole::CURATOR) {
            $tabs['all'] = Tab::make('Todas Empresas');
            $tabs['my_companies'] = Tab::make('Minhas Empresas')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->whereHas(
                        'company_info',
                        fn ($q) => $q->where('curator_id', $user->id)
                    )
                );
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth(Width::ExtraLarge)->after(fn (User $record) => CompanyInfo::create(['curator_id' => Auth::id(), 'company_id' => $record->id])),
        ];
    }
}
