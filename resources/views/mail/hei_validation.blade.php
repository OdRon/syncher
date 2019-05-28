<p>
	{{ $name }},
	<br />
	@if($samples->isEmpty())

		You have successfully followed and documented online all your HIV Exposed Infants for {{ $time_period }}.
		<br />
		<br />

		Also find attached the HEI Validation Tool, to guide you on how to update the HEI Follow Up Status.
		<br />
		<br />

		Please find attached the summary report.
		<br />
		<br />

		Please Log in to <a href='http://eid.nascop.org/'>Eid</a> with your {{ $division }} Credentials, to download the detailed report in Excel format.
		<br />

		If over the continuum of care the patient status changes e.g. LTFU to Initiated or Died , you can edit this on the 'Patient Follow Up' Link on view listings.
		<br />

		Thanks.
		<br />

		--
		<br />

		EID Support Team
		<br />

		This email was automatically generated. Please do not respond to this email address or it will be ignored.

	@else

		Please find attached the HIV Exposed Infants Report pending online documentation for {{ $time_period }}.
		<br />
		<br />

		Please note that the 2nd/3rd PCR that turned <b>Positive</b> have also been added to the list for follow up and documentation online.
		<br />
		<br />

		Also find attached the HEI Validation Tool, to guide you on how to update the HEI Follow Up Status.
		<br />
		<br />

		Please Log in to <a href='https://eid.nascop.org/'>Eid</a> with your {{ $division }} Credentials, to download the same Report in Excel format.
		<br />
		<br />

		Once you have the Status ( Enrolled (with CCC # & Date Initiated onto Treatment), Lost to Follow up ,Died , Adult Sample , Transferred Out , Other reasons e.g Denial ) of the infant, update the same within your {{ $division }} Login Page under the  'Patient Follow Up' Link.
		<br />
		<br />

		OR
		<br />
		<br />

		<a href="https://eiddash.nascop.org/">Click here</a>  provided below to directly access the page to update the status of the infants tracked.
		Thanks.
		<br />
		<br />

		--
		<br />
		<br />

		EID Support Team
		<br />

		This email was automatically generated. Please do not respond to this email address or it will be ignored.

	@endif
</p>