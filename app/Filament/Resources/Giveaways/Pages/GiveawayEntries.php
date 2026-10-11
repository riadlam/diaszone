<?php

namespace App\Filament\Resources\Giveaways\Pages;

use App\Filament\Resources\Giveaways\GiveawayResource;
use App\Models\GiveawayEntry;
use App\Services\GiveawayTelegramNotifier;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class GiveawayEntries extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = GiveawayResource::class;

    protected string $view = 'filament.resources.giveaways.pages.entries';

    protected static ?string $title = 'Entries';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Entries — '.$this->getRecord()->title;
    }

    public function getBreadcrumb(): string
    {
        return 'Entries';
    }

    public function table(Table $table): Table
    {
        $giveawayId = $this->getRecord()->getKey();

        return $table
            ->query(fn (): Builder => GiveawayEntry::query()
                ->where('giveaway_id', $giveawayId)
                ->with(['user', 'referrals']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Player')
                    ->description(fn (GiveawayEntry $record): ?string => $record->user?->email)
                    ->searchable(),

                ImageColumn::make('screenshot_path')
                    ->label('Screenshot')
                    ->disk('public')
                    ->height(56)
                    ->square(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'declined' => 'danger',
                        'draft' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('referral_code')
                    ->label('Code')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('referrals_count')
                    ->counts('referrals')
                    ->label('Referrals')
                    ->badge(),

                TextColumn::make('referral_satisfied_at')
                    ->label('Referral OK')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                TextColumn::make('tlg_message_id')
                    ->label('Telegram ID')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'declined' => 'Declined',
                    ]),
            ])
            ->recordActions([
                Action::make('view_image')
                    ->label('View image')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->visible(fn (GiveawayEntry $record) => $record->hasScreenshot())
                    ->modalHeading('Screenshot capture')
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (GiveawayEntry $record) => new HtmlString(
                        view('filament.giveaways.screenshot-preview', ['entry' => $record])->render()
                    )),

                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (GiveawayEntry $record) => $record->status === GiveawayEntry::STATUS_PENDING && $record->hasScreenshot())
                    ->modalHeading('Approve entry')
                    ->modalDescription('Review the screenshot below, then approve.')
                    ->modalWidth('4xl')
                    ->modalContent(fn (GiveawayEntry $record) => new HtmlString(
                        view('filament.giveaways.screenshot-preview', ['entry' => $record])->render()
                    ))
                    ->action(function (GiveawayEntry $record) {
                        $record->update([
                            'status' => GiveawayEntry::STATUS_APPROVED,
                            'decline_reason' => null,
                            'reviewed_at' => now(),
                            'reviewed_by' => Auth::id(),
                        ]);

                        app(GiveawayTelegramNotifier::class)->sync($record->fresh(['user', 'giveaway', 'referrals.referredUser']));

                        Notification::make()->title('Entry approved')->success()->send();
                    }),

                Action::make('decline')
                    ->label('Decline')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (GiveawayEntry $record) => $record->status === GiveawayEntry::STATUS_PENDING)
                    ->modalHeading('Decline entry')
                    ->modalDescription('Review the screenshot, then explain what the player must fix.')
                    ->modalWidth('4xl')
                    ->modalContent(fn (GiveawayEntry $record) => new HtmlString(
                        view('filament.giveaways.screenshot-preview', ['entry' => $record])->render()
                    ))
                    ->form([
                        Textarea::make('decline_reason')
                            ->label('Reason shown to the player')
                            ->required()
                            ->rows(3)
                            ->default('Please check the giveaway conditions (like, follow, 5 tags screenshot) and try again.'),
                    ])
                    ->action(function (GiveawayEntry $record, array $data) {
                        $record->update([
                            'status' => GiveawayEntry::STATUS_DECLINED,
                            'decline_reason' => $data['decline_reason'],
                            'reviewed_at' => now(),
                            'reviewed_by' => Auth::id(),
                        ]);

                        app(GiveawayTelegramNotifier::class)->sync($record->fresh(['user', 'giveaway', 'referrals.referredUser']));

                        Notification::make()->title('Entry declined')->danger()->send();
                    }),
            ]);
    }
}
