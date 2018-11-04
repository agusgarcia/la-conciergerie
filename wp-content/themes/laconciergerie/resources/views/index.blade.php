@extends('layouts.app')

@section('content')
    @include('partials.page-header')

    @foreach(NewsArchive::LastPosts(-1) as $news)
        @include('components.news-item', $news)
    @endforeach
    <div class="post__navigation">
        {!! get_the_posts_navigation() !!}
    </div>
@endsection
