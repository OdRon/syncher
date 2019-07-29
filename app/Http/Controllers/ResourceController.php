<?php

namespace App\Http\Controllers;
use App\Resource;

use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $resources = Resource::get();
        return view('tables.resources', compact('resources'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('forms.resource');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->only('name');
        if (null !== $request->resource){
            $filename = time();
            $filenameWithExtension = $filename.'.'.$request->resource->getClientOriginalExtension();
            $request->resource->move(public_path('/resource/'), $filenameWithExtension);
            $filenameWithExtension = '/resource/'.$filenameWithExtension;
            $data['uri'] = $fullfilename;
            $data['link'] = env('APP_URL').'/download'.$fullfilename;
            $data['file'] = $filename;
            $resource = new Resource;
            $resource->fill($data);
            $resource->save();
        } else {
            session(['toast_message' => 'Resource Document not provided', 'toast_error' => 1]);
        }
        return redirect('files');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $resource = Resource::findOrFail($id);
        return view('forms.resource', compact('resource'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    { 
        $data = $request->only('name');
        if (null !== $request->resource){
            $filename = time();
            $filenameWithExtension = $filename.'.'.$request->resource->getClientOriginalExtension();
            $request->resource->move(public_path('/resource/'), $filenameWithExtension);
            $fullfilename = '/resource/'.$filenameWithExtension;
            $data['uri'] = $filename;
            $data['link'] = env('APP_URL').'/download'.$fullfilename;
        }
        $resource = Resource::findOrFail($id);
        $resource->fill($data);
        $resource->save();
        return redirect('files');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
