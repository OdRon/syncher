<style type="text/css">
    body.light-skin #side-menu li a {
        font-weight: 380;
    }
    body.light-skin #side-menu li a {
        color: black;
    }
    hr {
        margin-top: 0px;
        margin-bottom: 0px;
    }
    #menu {
        background-color: white;
    }
</style>
<aside id="menu">
    <div id="navigation">
        <ul class="nav" id="side-menu" style=" padding-top: 12px;padding-left: 8px;">
        @if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 10)
            <li><a href="{{ url('user/add') }}">Add Users</a></li>
            <hr />
        @endif
        @if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 10)
            <li><a href="{{ url('reports/permission/setup') }}">Reports Setup</a></li>
            <hr />
            <li><a href="http://lab-2.test.nascop.org/resource">Resources</a></li>
            <hr />
            <li><a href="{{ url('user/passwordReset') }}">Change Password</a></li>
            <hr />
        @elseif(Auth::user()->user_type_id == 9)
            <li><a href="{{ url('reports/support') }}">Downloadable Reports</a></li>
            <hr />
            <li><a href="{{ url('reports/remotelogin/EID') }}">EID Remote Log In Reports</a></li>
            <hr />
            <li><a href="{{ url('reports/remotelogin/VL') }}">VL Remote Log In Reports</a></li>
            <hr/>
            <li><a href="{{ url('#') }}">No Data Summary Reports</a></li>
            <hr/>
            {{-- <li><a href="{{ url('reports/nodata/EID') }}">EID No Data Reports</a></li>
            <hr />
            <li><a href="{{ url('reports/nodata/VL') }}">VL No Data Reports</a></li>
            <hr /> --}}
            <li><a href="{{ url('reports/utilization/EID') }}">EID Equipment Utilization Report</a></li>
            <hr />
            <li><a href="{{ url('reports/utilization/VL') }}">VL Equipment Utilization Report</a></li>
            <hr />
            <li><a href="{{ url('#') }}">EID No Data Sample Listing</a></li>
            <hr />
            <li><a href="{{ url('#') }}">VL No Data Sample Listing</a></li>
            <hr />
        @elseif (Auth::user()->user_type_id != 12)
            @if(Auth::user()->user_type_id == 8)
                <li><a href="{{ url('results/EID') }}">EID Batch Results</a></li>
                <hr />
                <li><a href="{{ url('results/VL') }}">VL Batch Results</a></li>
                <hr />
            @else
                @if(in_array(Auth::user()->user_type_id, [14, 15]))
                    <li><a href="{{ url('lab/allocation') }}">Allocation List</a></li>
                    <hr />
                    <li><a href="{{ url('lab/consumption') }}">Consumption Reports List</a></li>
                    <hr />
                @else
                    <li><a href="{{ url('reports/EID') }}">EID Results/Reports</a></li>
                    <hr />
                    <li><a href="{{ url('reports/VL') }}">VL Results/Reports</a></li>
                    <hr />
                @endif
            @endif
            @if(!in_array(Auth::user()->user_type_id, [2, 6, 7, 14, 15]))
                <li><a href="{{ url('hei/validate') }}">HEI Patient Follow Up</a></li>
                <hr />
                <li><a href="{{ url('#') }}">HEI Validation Guide</a></li>
                <hr />
                @if(Auth::user()->user_type_id == 8)
                <li><a href="{{ url('patients/EID') }}">EID Patient List</a></li>
                <hr />
                <li><a href="{{ url('patients/VL') }}">VL Patient List</a></li>
                <hr />
                @endif
                @if(Auth::user()->user_type_id != 8)
                    <li><a href="{{ url('sites') }}">Facilities</a></li>
                    <hr />
                    <li><a href="#">User Guide</a></li>
                    <hr />
                @endif
                @if(Auth::user()->user_type_id == 3 || Auth::user()->user_type_id == 8 || Auth::user()->user_type_id == 10)
                    <li>
                        <a href="http://lab-2.test.nascop.org/download/eid_req">EID Requisition Form</a>
                    </li>
                    <hr />
                    <li>
                        <a href="http://lab-2.test.nascop.org/download/vl_req">VL Requisition Form</a>
                    </li>
                    <hr />
                @endif
            @endif
            @if(in_array(Auth::user()->user_type_id, [10, 13]))
                <li>
                    <a href="{{ url('email/create') }}">Add Email</a>
                </li>                
                <li>
                    <a href="{{ url('email') }}">View Emails</a>
                </li>                
            @endif 
            @if(Auth::user()->user_type_id == 2 || Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 7)
                <li><a href="https://eid.nascop.org">EID Summaries</a></li>
                <hr />
                <li><a href="https://viralload.nascop.org">VL Summaries</a></li>
                <hr />
            @endif
            
            @if(Auth::user()->user_type_id != 8)
                <li><a href="{{ url('user/passwordReset') }}">Change Password</a></li>
                <hr />
            @endif
            @if(!in_array(Auth::user()->user_type_id, [6, 7, 2, 14, 15]))
                @if(Auth::user()->user_type_id != 8)
                <li><a href="#"><select class="form-control" id="sidebar_facility_search"></select></a></li>
                @endif
                <li><a href="#"><select class="form-control" id="sidebar_batch_search"></select></a></li>
                <li><a href="#"><select class="form-control" id="sidebar_patient_search"></select></a></li>
            @endif
        @elseif(Auth::user()->user_type_id == 12)
            <li><a href="{{ url('allocations/EID') }}">EID Allocation List</a></li>
            <hr />
            <li><a href="{{ url('allocations/VL') }}">VL Allocation List</a></li>
            <hr />
            <li><a href="{{ url('allocations/Consumables') }}">Consumable Allocation List</a></li>
            <hr />
            <li><a href="{{ url('allocationdrfs') }}">DRFs</a></li>
            <hr />
            <li><a href="{{ url('labcontacts') }}">Lab Contacts List</a></li>
            <hr />
        @endif
        </ul>
    </div>
</aside>