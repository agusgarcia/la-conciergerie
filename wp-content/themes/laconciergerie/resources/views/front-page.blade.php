@extends('layouts.app')
@section('content')
    @include('partials.page-header')
    NOW : {{ $current_exhibition->post_title }} - Du {{ App::formattedDate($current_exhibition->opening_date) }}
    au {{ App::formattedDate($current_exhibition->closing_date) }}
    <br>
    @if($upcoming_event)
        Event :  {{ $upcoming_event->post_title }}
    @endif
    <hr>

    @php($posts = $current_season)
    @foreach($posts as $item)
        {{--<div class="item @if($item->post_title == $current_exhibition) active @endif">--}}
        {{--@if($item->post_title == $current_exhibition)--}}
        {{--<strong>--}}
        {{--@endif--}}
        {{--@dump(get_post($post2->ID))--}}
        <br>
        <a href="{{ get_permalink($item) }}">
            {{--            @php(the_field('exhibition_date', $item->ID, true))--}}
            @if($item->exhibition_title)
                {{ $item->artist_name }},
                {{ $item->exhibition_title }}
                <br>
                {{ App::formattedDate($item->opening_date) }}
                - {{  App::formattedDate($item->exhibition_date_exhibition_date_closing) }}
                <hr>
            @else
                {{ $item->post_title }} -
                {{ App::formattedDate($item->event_date) }}
                <hr>
            @endif
        </a>
        {{--@if($item->post_title == $current_exhibition)--}}
        {{--</strong>--}}
        {{--@endif--}}
        {{--</div>--}}
    @endforeach
    <section>

        @foreach($last_news as $news)
            <div>
                <h2>{{ $news->post_title }}</h2>
                <time class="updated"
                      datetime="{{  $news->post_date }}">{{ App::formattedDate($news->post_date) }}</time>
                <br>
                <p>{!! $news->post_content !!}</p>
                <a href="{{ get_permalink($news) }}">(Voir plus ?)</a>
            </div>
        @endforeach

        <a href="{{  get_permalink( get_option( 'page_for_posts' ) ) }}" title="News">Voir toutes les news</a>

    </section>

    <hr>

    <section class="mediation">
        <ul>

        @foreach($last_mediation_posts as $post)
            {{-- TODO: Create "single-post-component" (& single-mediation)--}}
            <li> @include('mediation.mediation-item', $post) </li>
        @endforeach
        </ul>
        <a href="{{  get_post_type_archive_link('mediation') }}" title="Mediation">Voir toutes les visites</a>

    </section>

@endsection
