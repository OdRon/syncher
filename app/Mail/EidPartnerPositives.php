<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mpdf\Mpdf;

use DB;
use \App\SampleView;
use \App\SampleAlertView;

class EidPartnerPositives extends Mailable
{
    use Queueable, SerializesModels;


    public $summary;
    public $samples;
    public $title;
    public $name;
    public $division;
    public $path;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($partner_contact_id)
    {
        $contact = DB::table('eid_partner_contacts_for_alerts')->where('id', $partner_contact_id)->get()->first();
        $samples = SampleAlertView::where('facility_id', '!=', 7148)
            ->whereIn('pcrtype', [1, 2, 3])
            ->where(['result' => 2, 'repeatt' => 0, 'enrollment_status' => 0, 'partner_id' => $contact->partner])
            ->when(($contact->split == 1), function($query) use ($contact){
                return $query->where('county_id', $contact->county);
            })
            ->whereYear('datetested', date('Y'))
            ->orderBy('facility_id')
            ->orderBy('datetested', 'ASC')
            ->get();

        $facilities = SampleView::selectRaw("distinct facility_id")
            ->whereIn('pcrtype', [1, 2, 3])
            ->where(['result' => 2, 'repeatt' => 0])
            ->get()->toArray();

        $totals = SampleAlertView::selectRaw("facility_id, enrollment_status, facilitycode, facility, county, subcounty, partner, count(id) as total")
            ->whereIn('pcrtype', [1, 2, 3])
            ->where(['result' => 2, 'repeatt' => 0])
            ->whereIn('facility_id', $facilities)
            ->whereYear('datetested', date('Y'))
            ->groupBy('facility_id', 'enrollment_status')
            ->orderBy('facility_id')
            ->get();

        $data;

        foreach ($facilities as $i => $id) {
            $data[$i]['no'] = $i + 1;
            $data[$i]['mfl'] = $totals->where('facility_id', $id)->first()->facilitycode ?? '';
            $data[$i]['facility'] = $totals->where('facility_id', $id)->first()->facility ?? '';
            $data[$i]['county'] = $totals->where('facility_id', $id)->first()->county ?? '';
            $data[$i]['subcounty'] = $totals->where('facility_id', $id)->first()->subcounty ?? '';
            $data[$i]['partner'] = $totals->where('facility_id', $id)->first()->partner ?? '';

            $data[$i]['positives'] = $totals->where('facility_id', $id)->sum('total');

            $data[$i]['treatment'] = $totals->where('facility_id', $id)->where('enrollment_status', 1)->first()->total ?? 0;
            $data[$i]['ltfu'] = $totals->where('facility_id', $id)->where('enrollment_status', 2)->first()->total ?? 0;
            $data[$i]['dead'] = $totals->where('facility_id', $id)->where('enrollment_status', 3)->first()->total ?? 0;
            $data[$i]['adult'] = $totals->where('facility_id', $id)->where('enrollment_status', 4)->first()->total ?? 0;
            $data[$i]['transfer'] = $totals->where('facility_id', $id)->where('enrollment_status', 5)->first()->total ?? 0;
            $data[$i]['otherreasons'] = $totals->where('facility_id', $id)->where('enrollment_status', 6)->first()->total ?? 0;

            $data[$i]['unknown'] = $data[$i]['positives'] - ($data[$i]['treatment'] + $data[$i]['ltfu'] + $data[$i]['dead'] + $data[$i]['adult'] + $data[$i]['transfer'] + $data[$i]['otherreasons']);

           
           if($data[$i]['positives'] == 0) $data[$i]['unknown_percentage'] = 0;
           else{
                $data[$i]['unknown_percentage'] = (int) (($data[$i]['unknown'] / $data[$i]['positives']) * 100); 
           }
        }
        $this->summary = $data;
        $this->samples = $samples;
        $addendum = '';
        if($contact->split == 1) $addendum = " IN " . $data[0]['county'] . " COUNTY";

        if($samples->isEmpty()){
            $this->$title = date('Y') .  ' COMPLETED HEI FOLLOW UP SUMMARY FOR ' . $data[0]['partner'] . ' SITES ' . $addendum; 
        }
        else{
            $this->$title = date('Y') .  ' HEI FOR FOLLOW UP & ONLINE DOCUMENTATION FOR ' . $data[0]['partner'] . ' SITES ' . $addendum;             
        }
        $this->name = $data[0]['partner'];
        $this->division = 'partner';

        $path = storage_path('app/hei/partner/' . $contact->id .   ' .pdf');
        $this->path = $path;
        if(file_exists($path)) unlink($path);

        $pdf_data['summary'] = $data;
        $pdf_data['samples'] = $samples;
        $pdf_data['title'] = $title; 

        $mpdf = new Mpdf(['format' => 'A4-L']);
        $view_data = view('exports.hei_followup', $pdf_data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->attach($this->path);
        $this->attach(public_path('attachments/HEIValidationToolGuide.pdf'));
        return $this->view('mail.hei_validation');
    }
}
