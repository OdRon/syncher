<p>
	{{ $name }},
	<br />
	@if($nonsup_absent),

		No Samples Tested for the specified duration had > 1000 cp/ml  Outcomes ( Not Suppressed)
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

		Please find attached the Patients with  > 1000 cp/ml  Outcomes ( Not Suppressed) for the specified duration.

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