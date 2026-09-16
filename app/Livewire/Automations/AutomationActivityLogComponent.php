<?php

namespace App\Livewire\Automations;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use Spatie\Mailcoach\Domain\Automation\Models\Automation;
use Spatie\Mailcoach\Livewire\TableComponent;
use Spatie\Mailcoach\MainNavigation;

class AutomationActivityLogComponent extends TableComponent
{
    public Automation $automation;

    public function mount(Automation $automation): void
    {
        $this->automation = $automation;

        app(MainNavigation::class)->activeSection()?->add($automation->name, route('mailcoach.automations'));
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->query(fn () => Activity::query()->forSubject($this->automation))
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-s-clock')
            ->emptyStateDescription(__mc('No activity recorded yet'))
            ->columns([
                TextColumn::make('created_at')
                    ->label(__mc('Date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__mc('Event'))
                    ->badge(),
                TextColumn::make('causer.name')
                    ->label(__mc('By'))
                    ->default(__mc('System')),
                TextColumn::make('changes')
                    ->label(__mc('Changes'))
                    ->wrap()
                    ->getStateUsing(fn (Activity $record) => $this->describeChanges($record)),
            ]);
    }

    protected function describeChanges(Activity $record): string
    {
        $attributes = collect($record->attribute_changes['attributes'] ?? []);
        $old = collect($record->attribute_changes['old'] ?? []);

        return $attributes
            ->map(function (mixed $value, string $key) use ($old) {
                $new = $this->formatValue($value);

                if (! $old->has($key)) {
                    return "{$key}: {$new}";
                }

                return "{$key}: {$this->formatValue($old[$key])} → {$new}";
            })
            ->implode(', ');
    }

    protected function formatValue(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => 'empty',
            is_scalar($value) => (string) $value,
            default => json_encode($value),
        };
    }

    public function getLayout(): string
    {
        return 'mailcoach::app.automations.layouts.automation';
    }

    public function getLayoutData(): array
    {
        return [
            'automation' => $this->automation,
            'title' => $this->automation->name,
            'originTitle' => __mc('Automations'),
            'originHref' => route('mailcoach.automations'),
        ];
    }
}
