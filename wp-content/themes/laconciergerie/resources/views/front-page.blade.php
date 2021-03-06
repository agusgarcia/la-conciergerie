@extends('layouts.app')
@section('content')
    {{--{{ $global }}--}}
    <section class="currently">
        <div class="swiper-container currently__slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide" style="--color: {{ $current_exhibition->color }}">
                    {!! wp_get_attachment_image($current_exhibition->main_image, 'full', '', array("class" => "size-full currently__image")) !!}
                    <div class="currently__main">
                        <p class="currently__date">
                            <span>
                                {{ App::formattedDayAndMonth($current_exhibition->opening_date)[0] }}
                            </span>
                            <span>
                                {{ App::formattedDayAndMonth($current_exhibition->opening_date)[1] }}
                            </span>
                        </p>
                        <h2 class="currently__title">{{ $current_exhibition->artist_name }}</h2>
                        <h3 class="currently__subtitle">{{ $current_exhibition->exhibition_title }}</h3>
                    </div>
                    <div class="currently__info">
                        <p>Vernissage le {{ App::formattedDateWithDay($current_exhibition->opening_date) }}
                            à {{ $current_exhibition->preview_hour }}</p>
                        <p>Ouverture du
                            {{ App::formattedDateNoYear($current_exhibition->start_date) }}

                            au {{ App::formattedDate($current_exhibition->closing_date) }}</p>

                        <a class="button" href="{{ get_permalink($current_exhibition->ID) }}">En savoir plus <i
                                    class="icon-arrow">→</i> </a>
                    </div>
                </div>
                @if($upcoming_event)

                    <div class="swiper-slide" style="--color: {{ $upcoming_event->color }}">
                        {!! wp_get_attachment_image($upcoming_event->main_image, 'full', '', array("class" => "size-full currently__image")) !!}
                        {{ $upcoming_event->post_title }}
                        <div class="currently__main">
                            <p class="currently__date">
                            <span>
                                {{ App::formattedDayAndMonth($upcoming_event->opening_date)[0] }}
                            </span>
                                <span>
                                {{ App::formattedDayAndMonth($upcoming_event->opening_date)[1] }}
                            </span>
                            </p>
                            <h2 class="currently__title">{{ $upcoming_event->event_title }}</h2>
                            <h3 class="currently__subtitle"> {{ App::formattedDate($upcoming_event->opening_date) }}</h3>
                        </div>
                        <div class="currently__info">
                            <p>Le {{ App::formattedDateWithDay($upcoming_event->opening_date) }}
                                {{ $upcoming_event->event_hour }}</p>
                            <p>{{ $upcoming_event->event_place }}
                            </p>

                            <a class="button" href="{{ get_permalink($upcoming_event->ID) }}">En savoir plus <i
                                        class="icon-arrow">→</i> </a>
                        </div>
                    </div>
                @endif
            </div>
            <div class="swiper-pagination"></div>

            <div class="arrows arrows--big">
                <svg class="arrow arrow--reverse swiper-button-prev" xmlns="http://www.w3.org/2000/svg"
                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100">
                    <g transform="translate(0,-952.36218)">
                        <path d="m 49.99997,1018.5184 2.59376,-2.2188 28,-23.99996 -5.1875,-6.0937 -25.40626,21.78116 -25.4062,-21.78116 -5.1875,6.0937 28,23.99996 2.5937,2.2188 z"></path>
                    </g>
                </svg>
                <svg class="arrow swiper-button-next" xmlns="http://www.w3.org/2000/svg"
                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100">
                    <g transform="translate(0,-952.36218)">
                        <path d="m 49.99997,1018.5184 2.59376,-2.2188 28,-23.99996 -5.1875,-6.0937 -25.40626,21.78116 -25.4062,-21.78116 -5.1875,6.0937 28,23.99996 2.5937,2.2188 z"></path>
                    </g>
                </svg>
            </div>
            <span class="chevron--down js-scroll-season"></span>
        </div>

    </section>
    <section class="season" id="currentSeason">
        <div class="title__container">
            <h1 class="title">La saison</h1>
            <h2 class="subtitle">{!! App::currentSeason()  !!}</h2>
        </div>

        @php($posts = $current_season)
        <div class="row">
            <div class="swiper-container season__slider">
                <div class="swiper-wrapper">
                    @foreach($posts as $post)
                        <div class="swiper-slide @if($post->current)current @endif">
                            @if($post->exhibition_title)                                @include('components.exhibition-item', (array) $post)
                            @else
                                @include('components.event-item', (array) $post)
                            @endif
                        </div>
                    @endforeach

                </div>
                    <svg class="arrow arrow--reverse swiper-button-prev" xmlns="http://www.w3.org/2000/svg"
                         xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100">
                        <g transform="translate(0,-952.36218)">
                            <path d="m 49.99997,1018.5184 2.59376,-2.2188 28,-23.99996 -5.1875,-6.0937 -25.40626,21.78116 -25.4062,-21.78116 -5.1875,6.0937 28,23.99996 2.5937,2.2188 z"></path>
                        </g>
                    </svg>

                <div class="swiper-scrollbar"></div>
                <svg class="arrow swiper-button-next" xmlns="http://www.w3.org/2000/svg"
                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100">
                    <g transform="translate(0,-952.36218)">
                        <path d="m 49.99997,1018.5184 2.59376,-2.2188 28,-23.99996 -5.1875,-6.0937 -25.40626,21.78116 -25.4062,-21.78116 -5.1875,6.0937 28,23.99996 2.5937,2.2188 z"></path>
                    </g>
                </svg>
            </div>
        </div>
    </section>
    <section class="news news__container">
        <div class="title__container title__container--right">
            <h2 class="title title--right">Les news</h2>
        </div>
        @foreach(NewsArchive::LastPosts(2) as $news)
            @include('components.news-item', $news)
        @endforeach
        <div class="row button__container">
            <a class="button button--big" href="{{  get_permalink( get_option( 'page_for_posts' ) ) }}">Voir
                toutes les news</a>
        </div>

    </section>

    @include('components.last-mediation-items')
@endsection
