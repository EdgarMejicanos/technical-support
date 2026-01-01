<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn () => auth()->user()->isTecnico()),

                Forms\Components\Textarea::make('description')
                    ->label('Descripción del problema')
                    ->required()
                    ->columnSpanFull()
                    ->disabled(fn () => auth()->user()->isTecnico()),

                Forms\Components\Textarea::make('notes')
                    ->label('Notas internas')
                    ->placeholder('Notas adicionales, observaciones, diagnóstico, etc.')
                    ->columnSpanFull()
                    ->rows(4)
                    ->nullable(),


                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'pc' => 'Computadora',
                        'printer' => 'Impresora',
                        'other' => 'Otro',
                    ])
                    ->required()
                    ->disabled(fn () => auth()->user()->isTecnico()),

                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'waiting' => 'En espera',
                        'repairing' => 'En reparación',
                        'ready' => 'Listo',
                        'delivered' => 'Entregado',
                    ])
                    ->default('waiting')
                    ->required(),

                Forms\Components\Select::make('assigned_to')
                    ->label('Técnico asignado')
                    ->relationship(
                        name: 'technician',
                        titleAttribute: 'name'
                    )
                    ->searchable()
                    ->nullable()
                    ->disabled(fn () => auth()->user()->isTecnico()),

                Forms\Components\TextInput::make('total_amount')
                    ->label('Total a pagar')
                    ->numeric()
                    ->prefix('Q')
                    ->required()
                    ->reactive(),

                Forms\Components\TextInput::make('paid_amount')
                    ->label('Abono')
                    ->numeric()
                    ->prefix('Q')
                    ->default(fn ($record) => $record?->initial_amount ?? 0)
                    ->reactive(),

                Forms\Components\Placeholder::make('pending_amount')
                    ->label('Saldo pendiente')
                    ->content(function (callable $get) {
                        $total = (float) $get('total_amount');
                        $paid = (float) $get('paid_amount');

                        return 'Q' . number_format(max(0, $total - $paid), 2);
                    }),

                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required()

                    //creating clients from ticket form
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel(),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo')
                            ->email(),

                        Forms\Components\Textarea::make('address')
                            ->label('Dirección'),
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'pc',
                        'warning' => 'printer',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pc' => 'PC',
                        'printer' => 'Impresora',
                        'other' => 'Otro',
                    }),


                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'waiting',
                        'info' => 'repairing',
                        'success' => 'ready',
                        'gray' => 'delivered',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'waiting' => 'En espera',
                        'repairing' => 'En reparación',
                        'ready' => 'Listo',
                        'delivered' => 'Entregado',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),


                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por'),

                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Técnico'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('pending_amount')
                    ->label('Saldo pendiente')
                    ->money('GTQ')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pago')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                    ])
                    ->formatStateUsing(fn ($state) =>
                    $state === 'paid' ? 'Pagado' : 'Pendiente'
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        return $user->isAdmin() || $user->isTecnico();
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin();
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
