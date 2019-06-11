<?php
namespace App\Imports;

use Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Exports\NhrlExport;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Maatwebsite\Excel\Concerns\WithChunkReading;

class NhrlImport implements ToCollection/*, WithChunkReading, ShouldQueue*/
{
	public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {
        	dd($row);
        }
    }
    
    // public function chunkSize(): int
    // {
    //     return 1000;
    // }
}

?>