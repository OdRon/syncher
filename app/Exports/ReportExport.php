<?php

namespace App\Exports;

use DB;
use App\SampleView;
use App\Viralsample;
// use Maatwebsite\Excel\Concerns\FromQuery;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\Exportable;

class ReportExport //implements FromQuery/*, WithHeadings*/
{
 //    use Exportable;

 //    // private $title = [];
 //    // private $data;

 //    // public function __construct($title, $data){
 //    // 	$this->title = $title;
 //    // 	$this->data = $data;
 //    // }

 //    /**
 //    * @return heading array()
 //    */
	// // public function headings(): array
 // //    {
 // //        return $this->title;
 // //    }

 //    /**
 //    * @return \Illuminate\Support\Collection
 //    */
 //    public function query()
 //    {
 //        // dd(SampleView::query()->where('datetested', '=', '2008-09-16'));
 //    	return SampleView::query()->where('datetested', '=', '2008-09-16');
 //    }

    public static function generate($request) {
        self::query($request);
    }

    private static function query($request) {
        dd($request->all());
    }
}
