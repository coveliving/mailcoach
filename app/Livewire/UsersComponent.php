<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Mailcoach\Livewire\TableComponent;

class UsersComponent extends TableComponent
{
    public function getTitle(): string
    {
        return __mc('Users');
    }

    public function getLayout(): string
    {
        return 'mailcoach::app.layouts.settings';
    }

    public function getLayoutData(): array
    {
        return [
            'title' => __mc('Users'),
            'create' => 'user',
            'createText' => 'Create user',
            'createComponent' => CreateUserComponent::class,
        ];
    }

    public function deleteUser(User $user)
    {
        if ($user->id === Auth::user()->id) {
            $this->flashError(__mc('You cannot delete yourself!'));

            return;
        }

        $user->delete();

        notify(__mc('The user has been deleted.'));
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->query(User::query())
            ->recordUrl(fn (User $record) => route('users.edit', $record))
            ->defaultSort('email')
            ->columns($this->columns())
            ->recordActions($this->recordActions());
    }

    protected function columns(): array
    {
        return [
            TextColumn::make('email')
                ->label(__mc('Email'))
                ->extraAttributes(['class' => 'link'])
                ->sortable()
                ->searchable(),
            TextColumn::make('name')
                ->label(__mc('Name'))
                ->sortable()
                ->searchable(),
        ];
    }

    protected function recordActions(): array
    {
        return [
            Action::make('delete')
                ->icon('heroicon-s-trash')
                ->label('')
                ->color('danger')
                ->tooltip(__mc('Delete'))
                ->modalHeading(__mc('Delete'))
                ->requiresConfirmation()
                ->hidden(fn (User $record) => $record->id === Auth::user()->id)
                ->action(fn (User $record) => $this->deleteUser($record)),
        ];
    }
}
