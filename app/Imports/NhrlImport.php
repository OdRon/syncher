<?php
namespace App\Imports;

use Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Exports\NhrlExport;
use App\SampleView;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Maatwebsite\Excel\Concerns\WithChunkReading;

class NhrlImport implements ToCollection/*, WithChunkReading, ShouldQueue*/
{
	private $data = [];
	private $title = [];

	public function collection(Collection $rows)
    {
    	foreach ($rows as $key => $row) {
        	if ($key == 0){
        		$row[3] = 'db_mb_no';
        		$row[4] = 'HEI';
        		$this->title = $row;
        	} else {
        		$sample = SampleView::where('comment', 'like', $row[1])->first();
        		$this->data[$key] = $row;
        		$this->data[$key][3] = $sample->comment ?? null;
        		$this->data[$key][4] = $sample->patient ?? null;
        	}
        }
        Excel::store(new NhrlExport($this->data, $this->title), 'invoices.xlsx');
    }
    
    // public function chunkSize(): int
    // {
    //     return 1000;
    // }
}

?>