<?php

namespace App\Exports;

// use App\SampleView;
use Maatwebsite\Excel\Concerns\FromArray;
// use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class ReportExport implements FromArray/*, WithHeadings*/
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
        dd($this->data);
        // dd(SampleView::query()->where('datetested', '=', '2008-09-16'));
    	// return SampleView::query()->where('datetested', '=', '2008-09-16');
        return $this->data;
    }
}
