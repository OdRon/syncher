@extends('layouts.master')

@component('/forms/css')
    
@endcomponent

@section('css_scripts')

@endsection

@section('custom_css')
    <style type="text/css">
        .form-horizontal .control-label {
                text-align: left;
            }
        
    </style>
@endsection

@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table">
                            <thead>
                                <tr>
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Lab</th>
                                    <th rowspan="2">Address</th>
                                    <th colspan="2">First Contact</th>
                                    <th colspan="2">Second Contact</th>
                                    <th rowspan="2">Action</th>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <th>Telephone</th>
                                    <th>Name</th>
                                    <th>Telephone</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($contacts as $key => $contact)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $contact->lab->labdesc ?? 'KENYA MEDICAL and SUPPLIES AUTHORITY' }}</td>
                                    <td>{{ $contact->address }}</td>
                                    <td>{{ $contact->contact_person }}</td>
                                    <td>{{ $contact->telephone }}</td>
                                    <td>{{ $contact->contact_person_2 }}</td>
                                    <td>{{ $contact->telephone_2 }}</td>
                                    <td><center><a href="{{ route('labcontacts.edit', ['id' => $contact->id]) }}" class="btn btn-primary">Edit</a></center></td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><center>No Lab Allocation Contacts Data Available</center></td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('vendor/datatables/media/js/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
            <!-- DataTables buttons scripts -->
            <script src="{{ asset('vendor/pdfmake/build/pdfmake.min.js') }}"></script>
            <script src="{{ asset('vendor/pdfmake/build/vfs_fonts.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons-bs/js/buttons.bootstrap.min.js') }}"></script>
            
        @endslot
        
    @endcomponent
    <script type="text/javascript">
                
    </script>
   
@endsection