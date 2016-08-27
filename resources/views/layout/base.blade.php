<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $appName }}</title>

    <link href="{{ url('translator/css/app.css') }}" rel="stylesheet">

    <script>
        window.translator = {
            'baseUrl': "{{ route('translator.home') }}",
            'activeGroup': "{{ isset($group) ? $group : '' }}",
            'statuses': {
                'saved': {{ \Shemi\Translator\Models\Translation::STATUS_SAVED }},
                'changed': {{ \Shemi\Translator\Models\Translation::STATUS_CHANGED }}
            },
            'locales': {!! \Shemi\Translator\Locale::appLocales(true)  !!},
            'scopes': {!! json_encode(\Shemi\Translator\Finder::SCOPES, JSON_UNESCAPED_UNICODE) !!}
        }
    </script>

</head>
<body>

@include('translator::layout.partials.nav-bar')

<main class="container-fluid" role="main">
    <div class="row">
        <div class="col-md-2 sidebar">
            <translator-sidebar></translator-sidebar>
        </div>
        <div class="col-md-10">
            @yield('content')
        </div>
    </div>
</main>

<vs-alert
        :show.sync="showMainAlert"
        :duration="3100"
        :type="alertArgs.type"
        width="350px"
        placement="top-right"
        dismissable>
    {{--<span class="icon-info-circled alert-icon-float-left"></span>--}}
    <strong v-if="alertArgs.title">@{{ alertArgs.title }}</strong>
    <p v-if="alertArgs.description">@{{ alertArgs.description }}</p>
</vs-alert>

<vs-spinner id="spinner-box" :text.sync="spinnerMsg" v-ref:spinner></vs-spinner>

<script src="{{ url('translator/js/app.js') }}"></script>
</body>
</html>