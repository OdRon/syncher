<p>
	Hello {{ $credentials->name }},
	<br />
	The EID/VL system was undergoing maintenance from Thursday 30, 2018. Major changes have been done to the system including changes to the data encryption to the database. For this reason passwords have been changed/
	<br />

	Please Log in to <a href='http://eiddash.nascop.org/'></a> with your usual username but with a password of {{ $credentials->password }}. Once you log in please change you password to your desired password.
	<br />

	Please note your registered email is {{ $credentials->email }}
	<br />

	Thank you for your patience.
	<br />

	--
	<br />

	EID Support Team
	<br />

	This email was automatically generated. Please do not respond to this email address or it will be ignored.
</p>