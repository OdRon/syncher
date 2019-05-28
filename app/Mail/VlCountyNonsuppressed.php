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

class VlCountyNonsuppressed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $nonsup_absent;
    public $title;
    public $name;
    public $division;
    public $path;
    public $user_id;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        ini_set("memory_limit", "-1");

        $contact = DB::table('eid_users')->where('id', $this->user_id)->get()->first();

        $startdate = date('Y-m-d', strtotime('-7 days'));
        $enddate = date("Y-m-d", strtotime('-1 days'));

        $displayfromdate=date("d-M-Y",strtotime($startdate));
        $displaytodate=date("d-M-Y",strtotime($enddate));

        $range = strtoupper($displayfromdate . ' TO ' . $displaytodate);

        $samples = ViralsampleAlertView::where('facility_id', '!=', 7148)
            ->whereIn('rcategory', [1, 2, 3, 4])
            ->where(['repeatt' => 0, 'county_id' => $contact->partner])
            ->whereBetween('datetested', [$startdate, $enddate])
            ->orderBy('facility_id')
            ->orderBy('datetested', 'ASC')
            ->get();

        $i=0;
        $first = true;
        $nonsup_absent = true;
        $data = $non_suppressed = $adolescents = [];

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

            if($sample->pmtct == 1) $data[$i]['pregnant'] += 1;
            if($sample->pmtct == 2) $data[$i]['breast_feeding'] += 1;
            if($sample->age >= 10 && $sample->age <= 19) $data[$i]['adolescents'] += 1;
            if($sample->age < 10) $data[$i]['children'] += 1;
            if($sample->age > 20) $data[$i]['adults'] += 1;
            if($sample->age == 0) $data[$i]['no_age'] += 1;
        }

        $this->nonsup_absent = $nonsup_absent;
        $this->name = $data[0]['county'] ?? '';
        $this->division = 'County';

        if($nonsup_absent){ 
            $this->title = "NO INDIVIDUAL PATIENTS WITH OUTCOMES >1000cp/ml (Not Suppressed) FOR FOLLOW UP BETWEEN {$range} ";
        }
        else{
            $this->title = "NOT SUPPRESSED (>1000cp/ml) OUTCOMES  FOR SAMPLES TESTED BETWEEN {$range} " . strtoupper($this->name) . " COUNTY SITES";
        }

        $header = "<div align='center' style='text-align: center; align-content: center;'>
                        <img src=" . asset('img/naslogo.jpg') . " alt='NASCOP'>
                        <h3>MINISTRY OF HEALTH</h3>
                        <h3>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h3> 
                    </div>";
                    

        if(!is_dir(storage_path('app/suppression/county'))) mkdir(storage_path('app/suppression/county'), 0777, true);

        $path = storage_path('app/suppression/county/' . $contact->id .   '.pdf');
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
