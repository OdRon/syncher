<p>
	{{ $name }},
	<br />
	@if($nonsup_absent),

		No Samples Tested for the period between {{ $range ?? '' }} had &gt; 1000 cp/ml  Outcomes ( Not Suppressed)
		<br />

		Thanks.
		<br />

		--
		<br />

		VL Support Team
		<br />

		This email was automatically generated. Please do not respond to this email address or it will be ignored.
		<br />

	@else

		Please find attached the Patients with &gt; 1000 cp/ml  Outcomes (Not Suppressed) for the period between {{ $range ?? '' }}.

		<br />

		Thanks.

		<br />

		--

		<br />

		VL Support Team

		<br />

		This email was automatically generated. Please do not respond to this email address or it will be ignored.

	@endif
</p>