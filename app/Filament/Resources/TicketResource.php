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
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Descripción del problema')
                    ->required()
                    ->columnSpanFull(),

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
                    ->required(),

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
                    ->nullable(),
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
