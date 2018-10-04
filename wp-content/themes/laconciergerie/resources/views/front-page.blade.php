@extends('layouts.app')
@section('content')
    <div class="page-header">
        <h1>La saison</h1>
        <h2>2018-2019</h2>
    </div>
    NOW : {{ $current_exhibition->post_title }} - Du {{ App::formattedDate($current_exhibition->opening_date) }}
    au {{ App::formattedDate($current_exhibition->closing_date) }}
    <br>
    @if($upcoming_event)
        Event :  {{ $upcoming_event->post_title }}
    @endif
    <hr>

    @php($posts = $current_season)
    @foreach($posts as $post)
        {{-- TODO : Slider--}}
        @if($post->exhibition_title)
            @include('components.exhibition-item', (array) $post)
        @else
            @include('components.event-item', (array) $post)
        @endif
    @endforeach
    <section>

        @foreach(NewsArchive::lastPosts() as $news)
            @include('components.news-item', $news)
        @endforeach

        <a href="{{  get_permalink( get_option( 'page_for_posts' ) ) }}" title="News">Voir toutes les news</a>

    </section>

    <hr>

    <section class="mediation">

        @foreach(MediationArchive::lastPosts() as $post)
            @include('components.mediation-item', $post)
        @endforeach
        <a href="{{  get_post_type_archive_link('mediation') }}" title="Mediation">Voir toutes les visites</a>

    </section>

@endsection
