{{--
  Template Name: Archive Exhibitions Template
--}}

@extends('layouts.app')

@section('content')
    @include('partials.page-header')

    <div class="row archive__sort">
        Trier par
        <button class="js-sort-button active" data-sort="year">année</button>
        |
        <button class="js-sort-button" data-sort="name">nom de l'artiste</button>
    </div>

    <div class="row js-items-container sort__container active" data-sort="year">
        @foreach($posts_by_season as $post)
            @php($season_year = $post[0])
            @php($season_posts = $post[1])
            <h2 class="subtitle">Saison {{ $season_year }}</h2>
            <div class="item__container">
                @foreach($season_posts as $post)
                    @include('components.exhibition-item', (array) $post)
                @endforeach
            </div>
        @endforeach
    </div>
    <div class="row js-items-container sort__container" data-sort="name">
        @foreach ($posts_by_artist as $post)
            {{ $post->post_title }}
        @endforeach

        @foreach(array_keys($posts_by_artist) as $letter)
            <h2 class="subtitle">{{ $letter }}</h2>
            <div class="item__container">
                @foreach ($posts_by_artist[$letter] as $post)
                    @include('components.exhibition-item',(array) $post)
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
