<h2>Account Created</h2>
<p>Your testmaster account has been created. Here is your temporary password:</p>

<p>
	Username: {{ $user->username }} <small><em>(Or your email address)</em></small>
	Password: {{ $password }}
</p>

<strong>Login at {!! link_to('/') !!} to gain access to your account.</strong>