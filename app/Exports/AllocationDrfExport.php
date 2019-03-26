<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Allocation;

class AllocationDrfExport implements WithMultipleSheets
{
    use Exportable;

    protected $allocation;
    
    public function __construct(Allocation $allocation)
    {
        $this->allocation = $allocation;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
    	$sheets = [];

    	foreach ($allocation->details as $key => $detail) {
    		$sheets[] = new AllocationDetailsDrfExport($detail);
    	}

        return $sheets;
    }
}
