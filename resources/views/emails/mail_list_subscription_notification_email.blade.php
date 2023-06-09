@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ \horsefly\Model\Setting::get('site_name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    <p>A new contact <a href="{{ route('subscriber', ['uid' => $subscriber->uid, 'list_uid' => $subscriber->mailList->uid]) }}">{{ $subscriber->email }}</a> has been added to mail list <a href="{{ route('mail_list', ['uid' => $subscriber->mailList->uid]) }}">{{ $subscriber->mailList->name }}</a> </p>

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            {{ \horsefly\Model\Setting::get('site_name') }}
        @endcomponent
    @endslot
@endcomponent
