<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Maatwebsite\Excel\Concerns\WithChunkReading;

class NhrlImport implements ToCollection/*, WithChunkReading, ShouldQueue*/
{
	public function collection(Collection $rows)
    {
        dd($rows);
    }
    
    // public function chunkSize(): int
    // {
    //     return 1000;
    // }
}

?>