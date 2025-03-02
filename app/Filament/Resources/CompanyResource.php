<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Str;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\Wizard::make([
                   Forms\Components\Wizard\Step::make('Base Info')->schema([
                       Forms\Components\TextInput::make('title')->required()->maxLength(120),
                       Forms\Components\Select::make('user_id')->createOptionForm([
                           Forms\Components\Section::make([
                               Forms\Components\TextInput::make('name')->required(),
                               Forms\Components\TextInput::make('email')->required(),
                               Forms\Components\TextInput::make('password')->required()->hintAction(Forms\Components\Actions\Action::make('generate_password')->action(function (Forms\Set $set) {
                                   $password = Str::password(8);
                                   $set('password', $password);
                                   $set('password_confirmation', $password);
                               }))->dehydrated(fn(?string $state): bool => filled($state))->revealable()->configure()->same('password_confirmation')->password(),
                               Forms\Components\TextInput::make('password_confirmation')->revealable()->password()->required()
                           ])->columns()
                       ])->optionsLimit(10)->required()->label('CEO')->relationship('user', 'name')->searchable()->preload(),
                       Forms\Components\FileUpload::make('logo')->image(),
                       Forms\Components\FileUpload::make('company_registration_document'),
                       Forms\Components\Select::make('currency')->required()->required()->options(getCurrency())->searchable(),
                       Forms\Components\Select::make('country')->required()->required()->options(getCountry())->searchable(),
                       Section::make()
                           ->schema([
                               TextInput::make('daily_working_hours')->required()->label('Daily Working Hours')->numeric(),
                               select::make('weekend_days')->required()->label('Weekend Days')->multiple()->options([
                                   'saturday' => 'Saturday',
                                   'sunday' => 'Sunday',
                                   'monday' => 'Monday',
                                   'tuesday' => 'Tuesday',
                                   'wednesday' => 'Wednesday',
                                   'thursday' => 'Thursday',
                                   'friday' => 'Friday',
                               ])->placeholder('Select weekend days'),
                               Forms\Components\TextInput::make('overtime_rate')->required()->numeric(),
                           ])->columns(3),
                       Forms\Components\Textarea::make('contact_information')->maxLength(120),
                       Forms\Components\Textarea::make('address')->maxLength(120),
                       Forms\Components\Textarea::make('description')->columnSpanFull(),]
                   )->columns(2),
                   Forms\Components\Wizard\Step::make('Basic Setting')->schema([
                       Forms\Components\Repeater::make('departments')->relationship('departments')->schema([
                           Forms\Components\TextInput::make('title')->label('Department Name')
                               ->required()
                               ->maxLength(255)->columnSpanFull(),
                           Forms\Components\Textarea::make('description')->columnSpanFull()
                       ])->defaultItems(1)->maxItems(1)->required(),
                       Forms\Components\Repeater::make('Duty Type')->relationship('duties')->schema([
                           Forms\Components\TextInput::make('title')->columnSpanFull()
                               ->required()
                               ->maxLength(255),
                           Forms\Components\Textarea::make('description')
                               ->nullable()
                               ->columnSpanFull(),
                       ])->defaultItems(1)->maxItems(1)->required(),
                       Forms\Components\Repeater::make('Pay Frequency')->relationship('contracts')->schema([
                           Forms\Components\TextInput::make('title')
                               ->required()
                               ->maxLength(255),
                           Forms\Components\TextInput::make('day')
                               ->required()
                               ->numeric(),
                       ])->defaultItems(1)->maxItems(1)->required(),
                       Forms\Components\Repeater::make('Designation')->relationship('positions')->schema([
                           Forms\Components\TextInput::make('title')->label('Designation Title')
                               ->required()
                               ->columnSpanFull()
                               ->maxLength(255),
                           Forms\Components\FileUpload::make('document')->columnSpanFull(),
                           Forms\Components\Textarea::make('description')
                               ->columnSpanFull(),
                       ])->defaultItems(1)->required()->maxItems(1),
                   ])->columns(2)
               ])->columnSpanFull()
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->alignCenter(),
                Tables\Columns\TextColumn::make('title')->label('Company')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('country')->searchable()->alignCenter(),
                Tables\Columns\TextColumn::make('user.name')->label('CEO')->sortable()->alignCenter(),
                Tables\Columns\TextColumn::make('employee')->state(fn($record) => $record->employees->count())->label('Employees')->sortable()->alignCenter(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\Action::make('login')->tooltip('Login')->label('')->action(function ($record) {
//                    if ($record->user_id) {
//                        Auth::loginUsingId($record->user_id);
//                        return redirect('/admin');
//                    }
//                    \session()->push('super', \auth()->id());
//                    \session()->push('company', $record->user_id);
//                    return redirect('superAdmin/');

                  return  redirect(route('filament.admin.pages.dashboard',['tenant'=>$record->id]));
                })->icon('heroicon-s-user-circle')->iconSize(IconSize::Large)->requiresConfirmation()->modalHeading('Do want to login ?')->modalIcon('heroicon-s-user-circle')->modalSubmitActionLabel('Login'),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
