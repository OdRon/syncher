<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mpdf\Mpdf;

use DB;
use \App\ViralsampleAlertView;
use \App\Lookup;

class VlPartnerNonsuppressed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $nonsup_absent;
    public $title;
    public $name;
    public $division;
    public $path;
    public $partner_contact_id;
    public $range;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($partner_contact_id)
    {
        $this->partner_contact_id = $partner_contact_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        ini_set("memory_limit", "-1");
        $contact = DB::table('vl_partner_contacts_for_alerts')->where('id', $this->partner_contact_id)->get()->first();

        // $startdate = date('Y-m-d', strtotime('-21 days'));
        $startdate = date('Y-m-d', strtotime('-8 days'));
        $enddate = date("Y-m-d", strtotime('-1 days'));
        
        $displayfromdate=date("d-M-Y",strtotime($startdate));
        $displaytodate=date("d-M-Y",strtotime($enddate));

        $range = strtoupper($displayfromdate . ' TO ' . $displaytodate);
        $this->range = $range;

        $samples = ViralsampleAlertView::where('facility_id', '!=', 7148)
            ->whereIn('rcategory', [1, 2, 3, 4])
            ->where(['repeatt' => 0, 'partner_id' => $contact->partner])
            ->when(($contact->split == 1), function($query) use ($contact){
                return $query->where('county_id', $contact->county);
            })
            ->whereBetween('datetested', [$startdate, $enddate])
            ->orderBy('facility_id')
            ->orderBy('datetested', 'ASC')
            ->get();

        $i=0;
        $first = true;
        $nonsup_absent = true;
        $data = $non_suppressed = $viremia = $adolescents = [];

        foreach ($samples as $key => $sample) {
            if($first){
                $facility_id = $sample->facility_id;
                $first = false;
                $data[$i] = Lookup::filler($sample, $i);
            }
            if($facility_id != $sample->facility_id){
                $i++;
                $facility_id = $sample->facility_id;
                $data[$i] = Lookup::filler($sample, $i);
            }

            $data[$i]['total'] += 1;

            if($sample->rcategory == 3 || $sample->rcategory == 4){
                $nonsup_absent = false;
                $data[$i]['non_sup'] += 1;
                $non_suppressed[] = $sample;
                if($sample->age >= 10 && $sample->age <= 19) $adolescents[] = $sample;
            }

            if($sample->rcategory == 2) $viremia[] = $sample;
            if($sample->pmtct == 1) $data[$i]['pregnant'] += 1;
            if($sample->pmtct == 2) $data[$i]['breast_feeding'] += 1;
            if($sample->age >= 10 && $sample->age <= 19) $data[$i]['adolescents'] += 1;
            if($sample->age < 10) $data[$i]['children'] += 1;
            if($sample->age > 19) $data[$i]['adults'] += 1;
            if($sample->age == 0) $data[$i]['no_age'] += 1;
        }

        $this->nonsup_absent = $nonsup_absent;
        $this->name = DB::table('partners')->where('id', $contact->partner)->first()->name ?? '';
        $this->division = 'Partner';
        
        $addendum = '';
        if($contact->split == 1) $county = DB::table('countys')->where(['id' => $contact->county])->first()->name ?? '';
        if($contact->split == 1) $addendum = " IN " . strtoupper($county) . " COUNTY";

        if($nonsup_absent){ 
            $this->title = "NO INDIVIDUAL PATIENTS WITH OUTCOMES >1000cp/ml (Not Suppressed) FOR FOLLOW UP BETWEEN {$range} ";
        }
        else{
            $this->title = "NOT SUPPRESSED (>1000cp/ml) OUTCOMES  FOR SAMPLES TESTED BETWEEN {$range} " . strtoupper($this->name) . " SITES {$addendum}";
        }

        $header = "<div align='center' style='text-align: center; align-content: center;'>
                        <img src=" . asset('img/naslogo.jpg') . " alt='NASCOP'>
                        <h3>MINISTRY OF HEALTH</h3>
                        <h3>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h3> 
                    </div>";
                    

        if(!is_dir(storage_path('app/suppression/partner'))) mkdir(storage_path('app/suppression/partner'), 0777, true);

        $path = storage_path('app/suppression/partner/' . $contact->id .   '.pdf');
        $this->path = $path;
        if(file_exists($path)) unlink($path);

        $pdf_data = Lookup::get_viral_lookups();
        $pdf_data['summary'] = $data;
        $pdf_data['non_suppressed'] = $non_suppressed;
        $pdf_data['adolescents'] = $adolescents;
        $pdf_data['title'] = $this->title; 
        $pdf_data['range'] = $range; 

        $mpdf = new Mpdf(['format' => 'A4-L']);
        $view_data = view('exports.suppression', $pdf_data)->render();
        // $mpdf->SetHTMLHeader($header);
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);

        $this->attach($this->path, ['as' => $this->title . '.pdf']);
        return $this->subject($this->title)->view('mail.suppression');
    }
}
