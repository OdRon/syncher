{{ Form::open(['url' => url('reports/permission/setup'), 'method' => 'post', 'class'=>'form-horizontal', 'confirm_message' => 'Are you sure you would like to grant these rights']) }}
<div class="form-group">
    <label class="col-sm-4 control-label">User Types</label>
    <div class="col-sm-8">
        <select class="form-control lockable" required name="user_type_id" id="user_type_id">
            <option value=""> Select a User Type </option>
            @foreach ($usertypes as $usertype)
                <option value="{{ $usertype->id }}">{{ $usertype->user_type }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label class="col-sm-4 control-label">Report</label>
    <div class="col-sm-8">
        <select class="form-control lockable" required name="partner_report_id[]" id="partner_report_id[]" multiple="true">
            <option value=""> Select a Report Type </option>
            @foreach ($reports as $report)
                <option value="{{ $report->id }}">
                    {{ $report->name }}
                    @isset($report->testtype)
                        @if($report->testtype == 1) (EID) @else (VL) @endif
                    @endisset
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <div class="col-sm-8 col-sm-offset-4">
        <button class="btn btn-primary" type="submit">Grant Permission</button>
    </div>
</div>
{{ Form::close() }}