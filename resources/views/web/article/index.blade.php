@extends('web.layouts.app')

@section('content')
    @component('web.particals.jumbotron')
        <h3>{{ config('blog.article.title') }}</h3>

        <h6>{{ config('blog.article.description') }}</h6>
    @endcomponent

    @include('web.widgets.article')

    {{ $articles->links('web.pagination.default') }}

@endsection