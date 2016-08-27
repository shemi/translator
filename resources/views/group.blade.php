@extends('translator::layout.base')

@section('content')

    <div class="row">

        <div class="col-xs-12">

            <translator-group-translate group="{{ $group }}"></translator-group-translate>

        </div>
    </div>

@stop