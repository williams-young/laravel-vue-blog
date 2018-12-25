@extends('web.layouts.app')

@section('content')
    @component('web.particals.jumbotron')
        <h4>{{ request()->get('q') }}</h4>

        <h6>what you want to search.</h6>
    @endcomponent

    @include('web.widgets.article')

@endsection