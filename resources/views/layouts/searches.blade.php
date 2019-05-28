<script type="text/javascript">
	$(document).ready(function(){

		set_batch_select("sidebar_batch_search", "{{ url('/batch/search') }}", 1, "Search for batch", "{{ url('batchsearchresult') }}");
		
		set_select_patient("sidebar_patient_search", "{{ url('/patient/search') }}", 2, "Search for patient", "{{ url('patientsearchresult') }}");
		
		set_select_facility("sidebar_facility_search", "{{ url('/supportfacility/search') }}", 3, "Search for facility", "{{ url('facilitysearchresult') }}");
	});
	
	function set_batch_select(div_name, url, minimum_length, placeholder, send_url) {
		div_name = '#' + div_name;		

		$(div_name).select2({
			minimumInputLength: minimum_length,
			placeholder: placeholder,
			ajax: {
				delay	: 100,
				type	: "POST",
				dataType: 'json',
				data	: function(params){
					return {
						search : params.term
					}
				},
				url		: function(params){
					params.page = params.page || 1;
					return  url + "?page=" + params.page;
				},
				processResults: function(data, params){
					console.log(data);
					return {
						results 	: $.map(data.data, function (row){
							return {
								text	: row.name + ' - ' + row.type,
								id		: row.type + '/' +row.id		
							};
						}),
						pagination	: {
							more: data.to < data.total
						}
					};
				}
			}
		});
		
		set_change_listener(div_name, send_url);
	}
	
	function set_select_patient(div_name, url, minimum_length, placeholder, send_url=true) {
		div_name = '#' + div_name;		

		$(div_name).select2({
			minimumInputLength: minimum_length,
			placeholder: placeholder,
			ajax: {
				delay	: 100,
				type	: "POST",
				dataType: 'json',
				data	: function(params){
					return {
						search : params.term
					}
				},
				url		: function(params){
					params.page = params.page || 1;
					return  url + "?page=" + params.page;
				},
				processResults: function(data, params){
					return {
						results 	: $.map(data.data, function (row){
							return {
								text	: row.patient + ' - ' + row.type,
								id		: row.type + '/' + row.id		
							};
						}),
						pagination	: {
							more: data.to < data.total
						}
					};
				}
			}
		});
		if(send_url != false)
			set_change_listener(div_name, send_url);	
	}

	function set_select_facility(div_name, url, minimum_length, placeholder, send_url=false) {
		div_name = '#' + div_name;		

		$(div_name).select2({
			minimumInputLength: minimum_length,
			placeholder: placeholder,
			ajax: {
				delay	: 100,
				type	: "POST",
				dataType: 'json',
				data	: function(params){
					return {
						search : params.term
					}
				},
				url		: function(params){
					params.page = params.page || 1;
					return  url + "?page=" + params.page;
				},
				processResults: function(data, params){
					return {
						results 	: $.map(data.data, function (row){
							return {
								text	: row.facilitycode + ' - ' + row.name + ' (' + row.county + ')', 
								id		: row.id		
							};
						}),
						pagination	: {
							more: data.to < data.total
						}
					};
				}
			}
		});

		if(send_url != false)
			set_change_listener(div_name, send_url, false);
	}

	function set_change_listener(div_name, url, not_facility=true)
	{
		if(not_facility){
			// url = url.substring(0, url.length-7);
			// window.location.href = url + '/' + val;
		} 
		$(div_name).change(function(){
			var val = $(this).val();
			window.location.href = url + '/' + val;
		});	
	}

	function set_worksheet_change_listener(div_name, url)
	{
		url = url.substring(0, url.length-7);	
		$(div_name).change(function(){
			var val = $(this).val();
			window.location.href = url + '/find/' + val;
		});	
	}

</script>