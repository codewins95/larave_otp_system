
@component('mail::message')
    <p>Hi,</p>
    <p>Please user the following one time password (OTP) to access the form: {{ $otp }}</p>
    <p>Do not share this otp with anyone</p>
    <p><strong>Thank you! </strong></p>
    <p><strong>{{ config('app.name') }}</strong> </p>
@endcomponent
