@extends('layouts.app')
@section('content')
    @include('partials.page-header')
    NOW : {{ $current_exhibition->post_title }}
    <br>
    @if($upcoming_event)
        Event :  {{ $upcoming_event->post_title }}
    @endif
    <hr>
    @php(setlocale(LC_ALL, "fr_FR"))

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
                @php( $date = strtotime($item->opening_date) ) -
                {{ strftime('%e %B %Y', $date) }}
            @else
                {{ $item->post_title }} -
                @php( $date = strtotime($item->event_date) )
                {{ strftime('%e %B %Y', $date) }}

            @endif
        </a>
        {{--@if($item->post_title == $current_exhibition)--}}
        {{--</strong>--}}
        {{--@endif--}}
        {{--</div>--}}
    @endforeach
@endsection
