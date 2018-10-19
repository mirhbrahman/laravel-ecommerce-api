Welcome {{$user->name}}
You change your email please verify using the link:
{{route('verify', $user->verification_token)}}
