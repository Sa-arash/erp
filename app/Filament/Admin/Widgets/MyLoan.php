<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Pages\Auth\Login;
use App\Models\Employee;
use App\Models\Loan;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyLoan extends BaseWidget
{
    protected int | string | array $columnSpan='full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                    Loan::query()->where('employee_id',getEmployee()?->id)->orderBy('id','desc')
            )
            ->headerActions([
                Tables\Actions\Action::make('new')->label('Request Loan')->form([
                    Section::make([
                        TextInput::make('request_amount')->label('Request Amount')->columnSpanFull()->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric(),
                        Textarea::make('description')->nullable()->columnSpanFull()
                    ])->columns()
                ])->action(function ($data){
                    $company=getCompany();
                    $lastLoan=Loan::query()->where('company_id',$company->id)->orderBy('id','desc')->first();
                    $loan=Loan::query()->create([
                        'employee_id'=>getEmployee()->id,
                        'loan_code'=>generateNextCodeAsset($lastLoan?->loan_code ? $lastLoan->loan_code :"0001"),
                        'request_amount'=>$data['request_amount'],
                        'request_date'=>now(),
                        'company_id'=>$company->id,
                        'description'=>$data['description']
                    ]);
                    $CEO = Employee::query()->firstWhere('user_id', getCompany()->user_id);
                    if ($CEO){
                        $loan->approvals()->create([
                            'employee_id'=>$CEO->id,
                            'company_id'=>$company->id,
                            'position'=>'CEO'
                        ]);
                    }
                    Notification::make('success')->success()->title('Successfully Submitted')->send();
                })->requiresConfirmation()
            ])
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('loan_code')->label('Loan Code'),
                Tables\Columns\TextColumn::make('request_amount')->numeric()->label('Request Amount'),
                Tables\Columns\TextColumn::make('request_date')->label('Request Date'),
                Tables\Columns\TextColumn::make('answer_date')->label('Answer Date'),
                Tables\Columns\TextColumn::make('amount')->numeric()->label('Loan Amount'),
                Tables\Columns\TextColumn::make('number_of_installments')->label('Number of Installments'),
                Tables\Columns\TextColumn::make('number_of_payed_installments')->label('Number of Installments Pay'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
            ])->actions([
                Tables\Actions\ViewAction::make()
            ]);
    }
}
