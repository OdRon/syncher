<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // public function __construct(){
    //     Session::flush();

    //     Session::regenerate
    // }

    public function _columnBuilder($columns = null)
    {
        $column = '<tr>';
        if ($columns == null) {
            $column .= '<th><center>No Data available</center></th>';
        } else {
            foreach ($columns as $key => $value) {
                $column .= '<th>'.$value.'</th>';
            }
        }
        $column .= '</tr>';
        return $column;
    }

    public function dump_log($name, $api=true)
    {
        $api_path = '';
        if ($api)
            $api_path = 'api/';
        $path = 'app/logs/' . $api_path;
        // print_r($path);die();
        if(!is_dir(storage_path($path))) mkdir(storage_path($path), 0777);

        $postData = file_get_contents('php://input');
        
        $file = fopen(storage_path($path . $name .'.txt'), "a");
        if(fwrite($file, $postData) === FALSE) fwrite("Error: no data written");
        fwrite($file, "\r\n");
        fclose($file);


        try {
            $postData = json_decode($postData);
            return $postData;
        } catch (Exception $e) {
            print_r($e);
        }
        return $postData;
    }
}
