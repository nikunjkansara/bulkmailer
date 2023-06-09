@extends('layouts.black')

@section('title', trans('messages.edit_template'))

@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
@endsection

@section('content')
    <header>
		<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" style="height: 56px;">
			<a class="navbar-brand left-logo mr-0" href="#">
				@if (\horsefly\Model\Setting::get('site_logo_small'))
					<img src="{{ action('SettingController@file', \horsefly\Model\Setting::get('site_logo_small')) }}" alt="">
				@else
					<img height="22" src="{{ URL::asset('images/logo_light.png') }}" alt="">
				@endif
			</a>
			<div class="d-inline-block d-flex mr-auto align-items-center">
                <a style="" href="javascript:;" onclick="parent.$('body').removeClass('overflow-hidden');parent.$('.full-iframe-popup').fadeOut()" class="action black-back-button mr-3">
					<i class="material-icons-outlined">arrow_back</i>
				</a>
                <h1 class="">{{ $campaign->name }}</h1>
				<i class="material-icons-outlined automation-head-icon ml-2">web</i>
			</div>
			<div class="automation-top-menu">
                <a class="action mr-4" href="{{ action('CampaignController@builderPlainEdit', [
                    'uid' => $campaign->uid
                ]) }}">
                    {{ trans('messages.campaign.plain_text_editor') }}
                </a>
				<button class="btn btn-primary" onclick="$('#classic-builder-form').submit()">{{ trans('messages.save') }}</button>
			</div>
            <a href="javascript:;" onclick="parent.$('body').removeClass('overflow-hidden');parent.$('.full-iframe-popup').fadeOut()"
                class="action black-close-button ml-2" style="margin-right: -15px">
                <i class="material-icons-outlined">close</i>
            </a>
		</nav>
	</header>
    <form style="margin-top: 56px;" id="classic-builder-form" action="{{ action('CampaignController@builderClassic', $campaign->uid) }}" method="POST" class="form-validate-jqueryz">
        {{ csrf_field() }}

        <div class="row mr-0 ml-0">
            <div class="col-md-9 pl-0 pb-0 pr-0">
                @include('helpers.form_control', ['type' => 'textarea',
                    'class' => 'template-editor',
                    'name' => 'html',
                    'label' => '',
                    'value' => $campaign->render(),
                    'rules' => [],
                    'help_class' => 'campaign'
                ])             
            </div>
            <div class="col-md-3 pr-0 pb-0 sidebar pr-4 pt-4 pl-4" style="overflow:auto;background:#f5f5f5">
                @include('elements._tags', ['tags' => horsefly\Model\Template::tags($campaign->defaultMailList)])
            </div>            
        </div>   
    <form>

    <script>
        $('#classic-builder-form').submit(function(e) {
            e.preventDefault();

            tinymce.triggerSave();

            var url = $(this).attr('action');
            var data = $(this).serialize();

            if ($(this).valid()) {
                // open builder effects
                addMaskLoading("{{ trans('messages.automation.template.saving') }}", function() {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: data,
                        statusCode: {
                            // validate error
                            400: function (res) {
                                removeMaskLoading();
                                
                                // notify
                                parent.notify('error', '{{ trans('messages.notify.error') }}', res.responseText);
                            }
                        },
                        success: function (response) {
                            removeMaskLoading();

                            if (typeof(parent.builderSelectPopup) != 'undefined') {
                                parent.builderSelectPopup.hide();
                            }

                            // notify
                            parent.notify('success', '{{ trans('messages.notify.success') }}', response.message);
                        }
                    });
                });         
            }     
        });

        $('.sidebar').css('height', parent.$('.full-iframe-popup').height()-56);

        var editor;
        $(document).ready(function() {
            editor = tinymce.init({
                selector: '.template-editor',
                height: parent.$('.full-iframe-popup').height()-56,
                convert_urls: false,
                remove_script_host: false,
                forced_root_block: "",
                plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
                imagetools_cors_hosts: ['picsum.photos'],
                menubar: 'file edit view insert format tools table help',
                toolbar: 'acelletags | undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
                toolbar_sticky: true,
                toolbar_sticky: true,
                valid_elements : '*[*],meta[*]',
                extended_valid_elements : "meta[*]",
                valid_children : "+body[style],+body[meta],+div[h2|span|meta|object],+object[param|embed]",
                content_css: [
                    APP_URL.replace('/index.php','')+'/tinymce/skins/lightgray/content.fixed.css',
                ],
                external_filemanager_path:APP_URL.replace('/index.php','')+"/filemanager2/",
                filemanager_title:"Responsive Filemanager" ,
                external_plugins: { "filemanager" : APP_URL.replace('/index.php','')+"/filemanager2/plugin.min.js"},
                setup: function (editor) {
                    
                    /* Menu button that has a simple "insert date" menu item, and a submenu containing other formats. */
                    /* Clicking the first menu item or one of the submenu items inserts the date in the selected format. */
                    editor.ui.registry.addMenuButton('acelletags', {
                        text: '{{ trans('messages.editor.insert_tag') }}',
                        fetch: function (callback) {
                        var items = [];

                        @foreach(horsefly\Model\Template::tags($campaign->defaultMailList) as $tag)
                            items.push({
                                type: 'menuitem',
                                text: '{{ $tag["name"] }}',
                                onAction: function (_) {
                                    editor.insertContent('{{ "{".$tag["name"]."}" }}');
                                }
                            });
                        @endforeach

                        callback(items);
                        }
                    });
                }
            });
        });
    </script>
@endsection
