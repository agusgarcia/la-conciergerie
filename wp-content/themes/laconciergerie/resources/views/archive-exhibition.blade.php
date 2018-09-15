{{--
  Template Name: Archive Exhibitions Template
--}}

@extends('layouts.app')

@section('content')
    @include('partials.page-header')
    @include('partials.content-page')
    <div>
        @foreach($posts_by_season as $post)
            <h2>Saison {{ $post[0] }}</h2>
            @foreach($post[1] as $post2)
                {{ $post2->post_title }}
                <br>
                {!! wp_get_attachment_image(get_post($post2)->thumbnail) !!}
                <br>
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
                {!! wp_get_attachment_image(get_post($post)->thumbnail) !!}
                <br>
                {{ $post->post_title }}
                <br>
                {{  $post->exhibition_title }}
                <br>
            @endforeach
            <hr>
        @endforeach

    </div>
@endsection
