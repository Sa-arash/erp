<?php

namespace App\Filament\Admin\Resources\BankResource\Pages;

use App\Filament\Admin\Resources\BankResource;
use App\Models\Account;
use App\Models\Bank;
use Filament\Actions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\Rules\Unique;

class EditCash extends EditRecord
{
    protected static string $resource = BankResource::class;
    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make([
                TextInput::make('name')->required()->maxLength(254),
                TextInput::make('account_code')->default(function () {
                    if (Bank::query()->where('company_id', getCompany()->id)->where('type', 1)->latest()->first()) {
                        return generateNextCode(Bank::query()->where('company_id', getCompany()->id)->latest()->first()->account_code);
                    } else {
                        return "001";
                    }
                })->prefix(fn(Get $get) => Account::query()->firstWhere('id', getCompany()->account_bank)?->code)->required()->maxLength(255),
                Select::make('currency')->required()->required()->options(getCurrency())->searchable(),
            ])->columns(3),
            Textarea::make('description')->columnSpanFull(),
            Hidden::make('type')->default(1),
        ]);
    }
}
