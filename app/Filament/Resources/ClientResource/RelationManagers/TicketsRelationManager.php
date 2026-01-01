<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class TicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'tickets';

    protected static ?string $title = 'Boletas';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),

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
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('GTQ'),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Abono')
                    ->money('GTQ'),

                Tables\Columns\TextColumn::make('pending_amount')
                    ->label('Saldo pendiente')
                    ->money('GTQ')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva boleta'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }
}

