{{--
  Template Name: Archive Events Template
--}}

@extends('layouts.app')

@section('content')
    @include('partials.page-header')
    <div class="row sort__container active">
    @foreach($posts_by_season as $post)
        @php($season_year = $post[0])
        @php($season_posts = $post[1])
        @if($season_posts)
        <h2 class="subtitle">Saison {{ $season_year }}</h2>
        <div class="item__container">
            @foreach($season_posts as $post)
                @include('components.event-item', (array) $post)
            @endforeach
        </div>
            @endif
    @endforeach
    </div>
@endsection
