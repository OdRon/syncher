<?php

namespace App\Exports\Sheets;

use App\AllocationDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AllocationDetailsDrfExport implements FromCollection/* ,WithHeadings, ShouldAutoSize*/, WithTitle
{

    protected $allocation_detail;
    protected $master_data;

    public function __construct(AllocationDetail $allocation_detail, $master_data)
    {
        $this->allocation_detail = $allocation_detail;
        $this->master_data = $master_data;
    }

	/**
    * @return heading array()
    */
	// public function headings(): array
 //    {
 //        return [
 //            'No.',
 //            'Description of Goods',
 //            'Unit',
 //            'Product No',
 //            'Quantity',
 //            'TOTALS',
 //        ];
 //    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $machine = $this->allocation_detail->machine;
    	$data = [];
        $data[] = [
            ['title' => strtoupper($machine['machine'] . ' Distribution request form')],
            ['Delivery Address', 'FROM'],
            ['to_lab' => $this->master_data['to']['name'], 'from_lab' => $this->master_data['from']['name']],
            ['to_lab_address' => $this->master_data['to']['address'], 'from_lab_address' => $this->master_data['from']['address']],
            ['Contact Person 1', $this->master_data['to']['contact_name_1'], 'Contact Person 1', $this->master_data['from']['contact_name_1']],
            ['Tel number', $this->master_data['to']['telephone_1'], 'Tel number', $this->master_data['from']['telephone_1']]
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
