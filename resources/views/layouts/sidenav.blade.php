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
        @if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 4)
            <li><a href="{{ url('user/add') }}">Add Users</a></li>
            <hr />
        @endif
        @if (Auth::user()->user_type_id == 1)
            <li><a href="{{ url('user/passwordReset') }}">Change Password</a></li>
            <hr />
        @elseif(Auth::user()->user_type_id == 9)
            <li><a href="{{ url('reports/support') }}">Reports Download</a></li>
            <hr />
            <li><a href="{{ url('reports/nodata/EID') }}">EID No Data Reports</a></li>
            <hr />
            <li><a href="{{ url('reports/nodata/VL') }}">VL No Data Reports</a></li>
            <hr />
            <li><a href="{{ url('reports/utilization/EID') }}">EID Utilization Report</a></li>
            <hr />
            <li><a href="{{ url('reports/utilization/VL') }}">VL Utilization Report</a></li>
            <hr />
        @else
            @if(Auth::user()->user_type_id == 8)
                <li><a href="{{ url('results/EID') }}">EID Batch Results</a></li>
                <hr />
                <li><a href="{{ url('results/VL') }}">VL Batch Results</a></li>
                <hr />
            @else
                <li><a href="{{ url('reports/EID') }}">EID Results/Reports</a></li>
                <hr />
                <li><a href="{{ url('reports/VL') }}">VL Results/Reports</a></li>
                <hr />
            @endif
            @if(!(Auth::user()->user_type_id == 2 || Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 7))
                <li><a href="{{ url('hei/validate') }}">HEI Patient Follow Up</a></li>
                <hr />
                <li><a href="{{ url('#') }}">HEI Validation Guide</a></li>
                <hr />
                @if(Auth::user()->user_type_id != 2)
                    @if(Auth::user()->user_type_id != 8)
                        <li><a href="{{ url('sites') }}">Facilities</a></li>
                        <hr />
                        <li><a href="#">User Guide</a></li>
                        <hr />
                        <li><a href="{{ url('user/passwordReset') }}">Change Password</a></li>
                        <hr />
                    @endif
                @endif
                @if(Auth::user()->user_type_id == 3 || Auth::user()->user_type_id == 8)
                    <li>
                        <a href="{{ url('downloads/EID') }}">EID Requisition Form</a>
                    </li>
                    <hr />
                    <li>
                        <a href="{{ url('downloads/VL') }}">VL Requisition Form</a>
                    </li>
                    <hr />
                @endif
            @endif
            @if(Auth::user()->user_type_id == 2 || Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 7)
                <li><a href="https://eid.nascop.org">EID Summaries</a></li>
                <hr />
                <li><a href="https://viralload.nascop.org">VL Summaries</a></li>
                <hr />
            @endif
            
            @if(!(Auth::user()->user_type_id == 6 || Auth::user()->user_type_id == 7 || Auth::user()->user_type_id == 2))
                @if(Auth::user()->user_type_id != 8)
                <li><a href="#"><select class="form-control" id="sidebar_facility_search"></select></a></li>
                @endif
                <li><a href="#"><select class="form-control" id="sidebar_batch_search"></select></a></li>
                <li><a href="#"><select class="form-control" id="sidebar_patient_search"></select></a></li>
            @endif
        @endif
        </ul>
    </div>
</aside>