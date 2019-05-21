<?php

namespace App\Exports;

// use App\SampleView;
use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class ReportExport implements FromCollection/*, WithHeadings*/
{
    use Exportable;

    // private $title = [];
    private $data;

    public function __construct(/*$title,*/ $data){
    	// $this->title = $title;
    	$this->data = $data;
    }

    /**
    * @return heading array()
    */
	// public function headings(): array
 //    {
 //        return $this->title;
 //    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function array()
    {
        // dd($this->data);
        // dd(SampleView::query()->where('datetested', '=', '2008-09-16'));
    	// return SampleView::query()->where('datetested', '=', '2008-09-16');
        return collect($this->data);
    }
}
