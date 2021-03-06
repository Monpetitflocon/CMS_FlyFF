@extends('layout')

@section('title', trans('title.player_settings'))

@section('content')
    <div class="box">
        <div class="ui primary attached message">
            <h1 class="header"><i class="cog icon"></i>@yield('title')</h1>
        </div>
        <div class="ui attached fluid clearing segment">
            <div class="ui stackable grid">
                <div class="five wide column">
                    <div class="ui vertical menu">
                        <a href="{{ route('settings.general.index') }}" class="item activable">@lang('settings.general.index.title')</a>
                        <a href="{{ route('settings.game.account.index') }}" class="item activable">@lang('settings.game.account.index.title')</a>
                        <a href="{{ route('settings.history.index') }}" class="item activable">@lang('settings.history.title')</a>
                    </div>
                </div>
                <div class="eleven wide column">
                    @yield('settings_content')
                </div>
            </div>
        </div>
    </div>
@endsection