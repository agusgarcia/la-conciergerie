@extends('layouts.app')

@section('content')
    @include('partials.page-header')

    @foreach(NewsArchive::lastPosts(-1) as $news)
        @include('components.news-item', $news)
    @endforeach

    {!! get_the_posts_navigation() !!}
@endsection