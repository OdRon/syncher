<?php

namespace App\Exports\Sheets;

use App\AllocationDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\AfterSheet;

class AllocationDetailsDrfExport implements FromCollection/* ,WithHeadings*/, ShouldAutoSize, WithTitle, WithEvents
{

    protected $allocation_detail;
    protected $master_data;

    public function __construct(AllocationDetail $allocation_detail, $master_data)
    {
        $this->allocation_detail = $allocation_detail;
        $this->master_data = $master_data;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $machine = $this->allocation_detail->machine;
    	$data = [];
        $data[] = [
            [strtoupper($machine['machine'] . ' Distribution request form'), '', '', '', '', ''],
            ['Delivery Address', '', '', 'FROM', '', ''],
            [$this->master_data['to']['name'], '', '', $this->master_data['from']['name'], '', ''],
            [$this->master_data['to']['address'], '', '', $this->master_data['from']['address'], '', ''],
            ['Contact Person 1', '', $this->master_data['to']['contact_name_1'], 'Contact Person 1', '', $this->master_data['from']['contact_name_1']],
            ['Tel number', '', $this->master_data['to']['telephone_1'], 'Tel number', '', $this->master_data['from']['telephone_1']],
            ['','','','','','']
        ];
        $data[] = [
            'No.',
            'Description of Goods',
            'Unit',
            'Product No',
            'Quantity',
            'TOTALS',
        ];
        
    	foreach ($this->allocation_detail->breakdowns as $key => $allocation_breakdown) {
    		$data[] = [
    				'No.' => $key + 1,
    				'Description' => $allocation_breakdown->breakdown->name,
    				'Unit' => $allocation_breakdown->breakdown->unit ?? '',
    				'Product_no' => '',
    				'quantity' => $allocation_breakdown->allocated,
    				'total' => $allocation_breakdown->allocated,
    			];
    	}

    	return collect($data);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->getSheetTitle();
    }

    public function registerEvents(): array
    {
        return [
            BeforeExport::class  => function(BeforeExport $event) {
                $event->writer->setCreator(auth()->user()->full_name);
            },
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->mergeCells('A1:F1');
                $event->sheet->mergeCells('A2:C2');
                $event->sheet->mergeCells('D2:F2');
                $event->sheet->mergeCells('A3:C3');
                $event->sheet->mergeCells('D3:F3');
                $event->sheet->mergeCells('A4:C4');
                $event->sheet->mergeCells('D4:F4');
                $event->sheet->mergeCells('A5:B5');
                $event->sheet->mergeCells('D5:E5');
                $event->sheet->mergeCells('A6:B6');
                $event->sheet->mergeCells('D6:E6');
                $event->sheet->mergeCells('A7:F7');
            },
        ];
    }

    private function getSheetTitle() {
        $machine = $this->allocation_detail->machine;
        $testtype = '';
        if ($this->allocation_detail->testtype == 1)
            $testtype = 'EID';
        else if ($this->allocation_detail->testtype == 2)
            $testtype = 'VL';

        if(!isset($machine))
            $title = 'CONSUMABLES';
        else
            $title = $machine['machine'] . ' ' . $testtype;

        return strtoupper($title);
    }
}
