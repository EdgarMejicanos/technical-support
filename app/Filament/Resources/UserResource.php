<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Hash;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('role')
                    ->label('Rol')
                    ->options([
                        'admin'    => 'Administrador',
                        'tecnico'  => 'Técnico',
                        'atencion' => 'Atención al cliente',
                    ])
                    ->required()
                    ->default('atencion'),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn ($context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->label('Contraseña'),

                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->same('password')
                    ->required(fn ($context) => $context === 'create')
                    ->label('Confirmar contraseña'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Rol')
                    ->colors([
                        'danger'  => 'admin',
                        'warning' => 'tecnico',
                        'success' => 'atencion',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'admin'    => 'Administrador',
                        'tecnico'  => 'Técnico',
                        'atencion' => 'Atención',
                        default    => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->label('Creado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
