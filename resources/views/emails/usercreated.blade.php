@component('mail::message')
# National Login

Hi {{ $user->oname }},<br/>
Your user has been created with the details below:<br/>
<strong>Username</strong>:{{ $user->email }}<br />
<strong>Password</strong>:{{ env('MASTER_PASSWORD') }}<br />
Please click on the button below and login with these details.

@component('mail::button', ['url' => 'https://eiddash.nascop.org'])
National Login
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
