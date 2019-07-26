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
        if (null !== $request->resource){
            $imageName = time().'.'.$request->resource->getClientOriginalExtension();
            $request->resource->move(public_path('/resource/'), $imageName);
            $filename = '/resource/'.$imageName;
            $data['uri'] = $filename;
            $data['link'] = env('APP_URL').'/download'.$filename;
            $resource = new Resource;
            $resource->fill($data);
            $resource->save();
        } else {
            session(['toast_message' => 'Resource Document not provided', 'toast_error' => 1]);
        }
        return back();
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
            $imageName = time().'.'.$request->resource->getClientOriginalExtension();
            $request->resource->move(public_path('/resources/'), $imageName);
            $filename = '/resources/'.$imageName;
            $data['uri'] = $filename;
            $data['link'] = env('APP_URL').'/download'.$filename;
        }
        $resource = Resource::findOrFail($id);
        $resource->fill($data);
        $resource->save();
        return back();
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
