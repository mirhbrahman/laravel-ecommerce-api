Welcome {{$user->name}}
Thank you for creating your account. Please verify your account using the link:
{{route('verify', $user->verification_token)}}
