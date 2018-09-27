{{--
  Template Name: Archive Exhibitions Template
--}}

@extends('layouts.app')

@section('content')
    @include('partials.page-header')
    @include('partials.content-page')
    @php(setlocale(LC_ALL, "fr_FR"))

    <div>
        @foreach($posts_by_season as $post)
            @php($season_year = $post[0])
            @php($season_posts = $post[1])
            <h2>Saison {{ $season_year }}</h2>
            @foreach($season_posts as $post)
                @include('components.exhibition-item', $post)
            @endforeach
            <hr>
        @endforeach
    </div>
    <div>
        @foreach ($posts_by_artist as $post)
            {{ $post->post_title }}
        @endforeach

        @foreach(array_keys($posts_by_artist) as $letter)
            <h2>{{ $letter }}</h2>
            @foreach ($posts_by_artist[$letter] as $post)
                    @include('components.exhibition-item', $post)
            @endforeach
            <hr>
        @endforeach
    </div>
@endsection
