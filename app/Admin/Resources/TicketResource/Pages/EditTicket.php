<?php

namespace App\Admin\Resources\TicketResource\Pages;

use App\Admin\Resources\OrderProductResource;
use App\Admin\Resources\TicketResource;
use App\Admin\Resources\UserResource;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Actions as InfolistActions;
use Filament\Infolists\Components\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected static string $view = 'admin.resources.ticket-resource.pages.edit-ticket';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\MarkdownEditor::make('message')
                    ->label('Message')
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    // Save action
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->messages()->create([
            'user_id' => Auth::id(),
            'message' => $data['message'],
        ]);

        return $record;
    }

    // Clear form after save
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        parent::save($shouldRedirect, $shouldSendSavedNotification);

        $this->form->fill();
    }


    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Infolists\Components\TextEntry::make('user_id')
                    ->size(TextEntrySize::Large)
                    ->formatStateUsing(fn($record) => $record->user->name)
                    ->url(fn($record) => UserResource::getUrl('index', ['record' => $record->user]))
                    ->label('User ID'),
                Infolists\Components\TextEntry::make('subject')
                    ->size(TextEntrySize::Large)
                    ->label('Subject'),
                Infolists\Components\TextEntry::make('status')
                    ->size(TextEntrySize::Large)
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->color(fn($state) => match ($state) {
                        'open' => 'success',
                        'closed' => 'danger',
                        'replied' => 'gray',
                    })
                    ->label('Status'),
                Infolists\Components\TextEntry::make('priority')
                    ->size(TextEntrySize::Large)
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->color(fn($state) => match ($state) {
                        'low' => 'success',
                        'normal' => 'gray',
                        'high' => 'danger',
                    })
                    ->label('Priority'),
                Infolists\Components\TextEntry::make('department')
                    ->size(TextEntrySize::Large)
                    ->placeholder('No department')
                    ->label('Department'),

                Infolists\Components\TextEntry::make('assigned_to')
                    ->size(TextEntrySize::Large)
                    ->label('Assigned To')
                    ->placeholder('No assigned user')
                    ->formatStateUsing(fn($record) => $record->assignedTo->name),

                Infolists\Components\TextEntry::make('order_product_id')
                    ->size(TextEntrySize::Large)
                    ->label('Order Product')
                    ->url(fn($record) => $record->orderProduct ? OrderProductResource::getUrl('edit', ['record' => $record->orderProduct]) : null)
                    ->placeholder('No order product')
                    ->formatStateUsing(fn($record) => "{$record->orderProduct->product->name} - " . ucfirst($record->orderProduct->status)),

                InfolistActions::make([
                    Action::make('Edit')
                        ->form(function (Form $form) {
                            return $form
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'open' => 'Open',
                                            'closed' => 'Closed',
                                            'replied' => 'Replied',
                                        ])
                                        ->default('open')
                                        ->required(),
                                    Forms\Components\Select::make('priority')
                                        ->label('Priority')
                                        ->options([
                                            'low' => 'Low',
                                            'normal' => 'Normal',
                                            'high' => 'High',
                                        ])
                                        ->default('normal')
                                        ->required(),
                                    Forms\Components\Select::make('department')
                                        ->label('Department')
                                        ->options(config('settings.ticket_departments')),
                                    Forms\Components\Select::make('user_id')
                                        ->label('User')
                                        ->relationship('user', 'id')
                                        ->searchable()
                                        ->preload()
                                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                                        ->required(),
                                    Forms\Components\Select::make('assigned_to')
                                        ->label('Assigned To')
                                        ->relationship('assignedTo', 'id')
                                        ->searchable()
                                        ->preload()
                                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                                        ->required(),
                                    Forms\Components\Select::make('order_product_id')
                                        ->label('Order Product')
                                        ->relationship('orderProduct', 'id', function (Builder $query, Get $get) {
                                            // Join orders and match the user_id
                                            $query->join('orders', 'orders.id', '=', 'order_products.order_id')
                                                ->where('orders.user_id', $get('user_id'));
                                        })
                                        ->getOptionLabelFromRecordUsing(fn($record) => "{$record->product->name} - " . ucfirst($record->status))
                                        ->disabled(fn(Get $get) => !$get('user_id')),
                                ]);
                        })
                        ->fillForm(fn($record) => [
                            'status' => $record->status,
                            'priority' => $record->priority,
                            'department' => $record->department,
                            'user_id' => $record->user_id,
                            'assigned_to' => $record->assigned_to,
                            'order_product_id' => $record->order_product_id,
                        ])
                        ->action(function (array $data, Ticket $record): void {
                            $record->update($data);
                        })
                        ->icon('heroicon-o-pencil'),
                    Action::make('Delete')
                        ->color('danger')
                        // ->url(fn($record) => TicketResource::getUrl('delete', ['record' => $record]))
                        ->icon('heroicon-o-trash'),

                ])
            ]);
    }

    public function deleteMessage(TicketMessage $message): void
    {
        $message->delete();
    }
}