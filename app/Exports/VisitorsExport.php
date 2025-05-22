<?php

namespace App\Exports;

use App\Models\AppModelsVisitor;
use App\Models\VisitorRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VisitorsExport implements  FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        dd(1);
        return VisitorRequest::query()->orderBy('id','desc')->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Phone',
            'Organization',
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->phone,
            $row->organization,
        ];
    }
}
