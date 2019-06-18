<?php
namespace App\Imports;

use Excel;
// use Illuminate\Support\Collection;
// use Maatwebsite\Excel\Concerns\ToCollection;

use Maatwebsite\Excel\Concerns\ToModel;
// use App\Exports\NhrlExport;
use App\Nhrl;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Maatwebsite\Excel\Concerns\WithChunkReading;

class NhrlImport implements ToModel/*ToCollection, WithChunkReading, ShouldQueue*/
{
	private $data = [];
	private $title = [];
    private $name = '';

    public function __construct($name) {
        $this->name = $name;
    }

    public function model(array $row)
    {
        return new Nhrl([
            'c_posted' => $row[0],
            'label_id' => $row[1],
            'login_date' => $row[2],
        ]);
    }
	// public function collection(Collection $rows)
 //    {
 //    	foreach ($rows as $key => $row) {
 //        	if ($key == 0){
 //        		$row[3] = 'db_mb_no';
 //        		$row[4] = 'HEI';
 //        		$this->title = $row;
 //        	} else {
 //        		$sample = SampleView::where('comments', 'like', '%'.$row[1].'%')->first();
 //        		$this->data[$key] = $row;
 //        		$this->data[$key][3] = $sample->comment ?? null;
 //        		$this->data[$key][4] = $sample->patient ?? null;
 //        	}
 //        }
 //        Excel::store(new NhrlExport($this->data, $this->title), $this->name . '.xlsx');
 //    }
}

?>