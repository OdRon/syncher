<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;

class ReportGenericExport implements FromArray
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        $data = [
            [1, 2, 3],
            [4, 5, 6]
        ];
        
        return $data;
    }
}
