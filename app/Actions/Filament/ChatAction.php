<?php

namespace App\Actions\Filament;

use Closure;
use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\View\ActionsIconAlias;

class ChatAction extends Action
{
    protected ?Closure $redirectUrlUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'chat';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::chat.label'));

        $this->defaultColor('primary');

        $this->tableIcon(FilamentIcon::resolve(ActionsIconAlias::EDIT_ACTION) ?? Heroicon::ChatBubbleLeftRight);
    }

    public function redirectUrlUsing(?Closure $callback): static
    {
        $this->redirectUrlUsing = $callback;

        return $this;
    }
}
