<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
/**
 * 
 */
class NhrlExport implements FromArray ,WithHeadings, ShouldAutoSize
{
    use Exportable;

    protected $data;
    protected $title;

    public function __construct($data, $title){
    	$this->data = $data;
        $this->title = $title;
    }

    /**
    * @return heading array()
    */
    public function headings(): array
    {
        return $this->title;
    }

    public function array(): array
    {
        // dd($this->data);
        return $this->data;
    }
}
?>