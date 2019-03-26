<?php

namespace App\Exports;

use App\AllocationDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AllocationDetailsDrfExport implements FromCollection, WithHeadings, ShouldAutoSize
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
	public function headings(): array
    {
        return [
            'No.',
            'Description of Goods',
            'Unit',
            'Product No',
            'Quantity',
            'TOTALS',
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
    	$data = [];
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
}
