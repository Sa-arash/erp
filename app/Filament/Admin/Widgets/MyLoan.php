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
                Tables\Actions\Action::make('new')->disabled(function(){
//                   return getEmployee()->loans->whereIn('status',['progressed','accepted','Waiting'])->count();
                })->label('Loan Request ')->form([
                    Section::make([
                        TextInput::make('request_amount')->label('Required Amount')->columnSpan(2)->mask(RawJs::make('$money($input)'))->stripCharacters(',')->required()->numeric()->default(fn()=>getEmployee()->loan_limit),
                        TextInput::make('loan_code')->required()->default(function (){
                            $lastLoan=Loan::query()->where('company_id',getCompany()->id)->orderBy('id','desc')->first();
                           return generateNextCodeAsset($lastLoan?->loan_code ? $lastLoan->loan_code :"0001");
                        })->readOnly(),
                        Textarea::make('description')->nullable()->columnSpanFull()
                    ])->columns(3)
                ])->action(function ($data){
                    $company=getCompany();
                    $loan=Loan::query()->create([
                        'employee_id'=>getEmployee()->id,
                        'loan_code'=>$data['loan_code'],
                        'request_amount'=>$data['request_amount'],
                        'request_date'=>now(),
                        'company_id'=>$company->id,
                        'description'=>$data['description'],

                    ]);
                        sendAR(getEmployee(),$loan,$company);
                    Notification::make('success')->success()->title('Successfully Submitted')->send();
                })
            ])
            ->columns([
                Tables\Columns\TextColumn::make('')->rowIndex(),
                Tables\Columns\TextColumn::make('loan_code')->label('Loan Code'),
                Tables\Columns\TextColumn::make('request_amount')->numeric()->label('Requested Amount  '),
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
