@extends('layouts.backend')

@section('title', trans('messages.upgrade.title.upgrade'))

@section('page_script')
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')

    <div class="page-title">
        <ul class="breadcrumb breadcrumb-caret position-right">
            <li><a href="{{ action("Admin\HomeController@index") }}">{{ trans('messages.home') }}</a></li>
        </ul>
        <h1>
            <span class="text-gear"><i class="icon-key"></i> {{ trans('messages.upgrade.title.upgrade') }}</span>
        </h1>
    </div>

@endsection

@section('content')

    <div class="tabbable">
        @include("admin.settings._tabs")

        <div class="tab-content">
            <div class="row">
                <div class="col-md-6">
                    @if (session('alert-error'))
                        @include('elements._notification', [
                            'level' => 'warning',
                            'title' => 'Cannot upgrade',
                            'message' => session('alert-error')
                        ])
                    @endif

                    @if (isset($failed))
                        <p class="alert alert-warning">
                            {{ trans('messages.upgrade.error.something_wrong') }}
                        </p>

                        <h3>{{ trans('messages.upgrade.title.in_progress') }}</h3>
                        <p>{!! trans('messages.upgrade.error.cannot_write') !!}</p>
                        <p>
                            <pre>{!! implode("\n", $failed) !!}</pre>
                        </p>
                        <p>
                            <a link-confirm="{{ trans('messages.upgrade.upgrade_confirm') }}" href="{{ action('Admin\SettingController@doUpgrade') }}" type="button" class="btn bg-primary btn-icon" link-method="POST">
                                {{ trans('messages.upgrade.button.retry') }}
                            </a>
                            <a link-confirm="{{ trans('messages.upgrade.upgrade_cancel') }}" href="{{ action('Admin\SettingController@cancelUpgrade') }}" type="button" class="btn bg-grey btn-icon" link-method="POST">
                                {{ trans('messages.upgrade.button.cancel') }}
                            </a>
                        </p>
                    @elseif ($manager->isNewVersionAvailable())
                        <h3>{{ trans('messages.upgrade.title.upgrade_confirm') }}</h3>
                        <p>{!! trans('messages.upgrade.wording.upgrade', [ 'current' => "<code>{$manager->getCurrentVersion()}</code>", 'new' => "<code>{$manager->getNewVersion()}</code>" ]) !!}</p>
                        <p>
                            <a link-confirm="{{ trans('messages.upgrade.upgrade_confirm') }}" href="{{ action('Admin\SettingController@doUpgrade') }}" type="button" class="btn bg-primary btn-icon" link-method="POST">
                                {{ trans('messages.upgrade.button.upgrade_now') }}
                            </a>
                            <a link-confirm="{{ trans('messages.upgrade.upgrade_cancel') }}" href="{{ action('Admin\SettingController@cancelUpgrade') }}" type="button" class="btn bg-grey btn-icon" link-method="POST">
                                {{ trans('messages.upgrade.button.cancel') }}
                            </a>
                        </p>
                    @else
                        <h3>{{ trans('messages.upgrade.title.current') }}</h3>
                        <p>{!! trans('messages.upgrade.wording.upload', [ 'current' => "<code>{$manager->getCurrentVersion()}</code>" ]) !!}</p>
                        <p>Upgrade may not work correctly if the upgrade package file size exceeds the upload limit set by your hosting's PHP settings:</p>
                        <ul>
                            <li><code>post_max_size</code> <strong>{{ ini_get('post_max_size') }}</strong></li>
                            <li><code>upload_max_filesize</code> <strong>{{ ini_get('upload_max_filesize') }}</strong></li>
                        </ul>
                        <form action="{{ action('Admin\SettingController@uploadApplicationPatch') }}" class="form-validate-jquery" method="POST"  enctype="multipart/form-data">
                            {{ csrf_field() }}

                            @include('helpers.form_control', ['required' => true, 'type' => 'file', 'label' => '', 'name' => 'file', 'value' => 'Upload'])
                            <button class="btn bg-teal">{{ trans('messages.upload') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
