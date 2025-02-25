<?php

namespace App\Admin\Resources;

use App\Admin\Resources\GatewayResource\Pages;
use App\Models\Gateway;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class GatewayResource extends Resource
{
    protected static ?string $model = Gateway::class;

    protected static ?string $navigationGroup = 'Extensions';

    protected static ?string $navigationIcon = 'ri-secure-payment-line';

    protected static ?string $activeNavigationIcon = 'ri-secure-payment-fill';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }

    public static function form(Form $form): Form
    {
        $gateways = \App\Helpers\ExtensionHelper::getAvailableGateways();
        $options = \App\Helpers\ExtensionHelper::convertToOptions($gateways);
        $documentation = \App\Helpers\ExtensionHelper::getDocumentation();

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->unique(static::getModel(), 'name', ignoreRecord: true)
                    ->placeholder('Enter the name of the gateway'),
                Forms\Components\Select::make('extension')
                    ->label('Gateway')
                    ->required()
                    ->searchable()
                    ->unique(static::getModel(), 'extension', ignoreRecord: true)
                    ->options($options->options)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Select $component) => $component
                        ->getContainer()
                        ->getComponent('settings')
                        ->getChildComponentContainer()
                        ->fill())
                    ->placeholder('Select the type of the gateway'),

                Section::make('Documentation')
                    ->schema([
                        MarkdownEditor::make('description')
                            ->label('README.md')
                            ->afterStateHydrated(fn ($component) => $component->state($documentation))
                            ->disabled()
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->hidden(fn() => empty($documentation)),
                // this is $documentation = \App\Helpers\ExtensionHelper::getDocumentation();

                Section::make('Gateway Settings')
                    ->description('Specific settings for the selected gateway')
                    ->schema([
                        Grid::make()->schema(fn (Get $get): array => $options->settings[$get('extension')] ?? $options->settings['default'])->key('settings'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGateways::route('/'),
            'create' => Pages\CreateGateway::route('/create'),
            'edit' => Pages\EditGateway::route('/{record}/edit'),
        ];
    }
}
