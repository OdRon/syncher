<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Allocation;
use App\AllocationContact;

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
    	$general_drf_data = $this->getallocationlabdetails();
    	foreach ($this->allocation->details as $key => $detail) {
    		$sheets[] = new Sheets\AllocationDetailsDrfExport($detail, $general_drf_data);
    	}

        return $sheets;
    }

    private function getallocationlabdetails(){
    	$lab = $this->allocation->lab;
    	$lab_allocation_contact = $this->allocation->lab->allocation_contacts;
    	$kemsa_data = AllocationContact::where('lab_id', '=', 0)->first();
        // setBackground()
    	return collect([
    				'to' => [
	    				'name' => $lab->labdesc,
	    				'address' => $lab_allocation_contact->address,
	    				'contact_name_1' => $lab_allocation_contact->contact_person,
	    				'telephone_1' => $lab_allocation_contact->telephone,
	    				'contact_name_2' => $lab_allocation_contact->contact_person_2,
	    				'telephone_2' => $lab_allocation_contact->telephone_2,
	    			],
	    			'from' => [
	    				'name' => 'KENYA MEDICAL SUPPLIES AUTHORITY',
	    				'address' => $kemsa_data->address,
	    				'contact_name_1' => $kemsa_data->contact_person,
	    				'telephone_1' => $kemsa_data->telephone,
	    				'contact_name_2' => $kemsa_data->contact_person_2,
	    				'telephone_2' => $kemsa_data->telephone_2,
	    			]
    			]);
    }
}
