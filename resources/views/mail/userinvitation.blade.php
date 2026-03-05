<p><b>To: {{ $maildata['email'] }}</b></p>

<p>You have been invited by <b>{{ $maildata['invited_by'] }}</b> to sign up for our preaching plan software. Please click the link below to create your account and get started:</p>

Your new user will have permission to edit the following:

<p>{{ $maildata['permissions'] }}</p>

<a href="{{url('/')}}/register/invite/{{$maildata['token']}}">Create Account</a>

<p>If you did not request this invitation, you are welcome to delete this email, but the link will expire within a week.</p>

<p>Thank you!</p>
<a href="https://methodist.church.net.za">https://methodist.church.net.za</a>