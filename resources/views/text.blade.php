<x-mail::message>
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Regards,')
{{ config('app.name') }}
@endif

{{-- Unsubscribe --}}
@if($unsubscribeLink ?? false)
@lang("If you no longer want to receive this type of email in the future go to :link.\n", ['link' => $unsubscribeLink])
@endif
@if($unsubscribeLinkForAll ?? false)
@lang("To no longer receive any future emails go to :link.\n", ['link' => $unsubscribeLinkForAll])
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => $actionText,
    ]
) {{ $displayableActionUrl }}
</x-slot:subcopy>
@endisset
</x-mail::message>
