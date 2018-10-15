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
        </div>

    </section>
    <section class="season">
        <div class="title__container">
            <h1 class="title">La saison</h1>
            <h2 class="subtitle">2018-2019</h2>
        </div>

        @php($posts = $current_season)
        <div class="row">
            <div class="swiper-container season__slider">
                <div class="swiper-wrapper">
                    @foreach($posts as $post)
                        <div class="swiper-slide">
                            @if($post->exhibition_title)
                                @include('components.exhibition-item', (array) $post)
                            @else
                                @include('components.event-item', (array) $post)
                            @endif
                        </div>
                    @endforeach
                    @foreach($posts as $post)
                        <div class="swiper-slide @if($post->current)current @endif">
                            @if($post->exhibition_title)                                @include('components.exhibition-item', (array) $post)
                            @else
                                @include('components.event-item', (array) $post)
                            @endif
                        </div>
                    @endforeach

                </div>

                <div class="swiper-scrollbar"></div>
            </div>
        </div>
    </section>
    <section class="news news__container">
        <div class="title__container title__container--right">
            <h2 class="title title--right">Les news</h2>
        </div>
        @foreach(NewsArchive::lastPosts() as $news)
            @include('components.news-item', $news)
        @endforeach
        <div class="row button__container">
            <a class="button button--big" href="{{  get_permalink( get_option( 'page_for_posts' ) ) }}">Voir
                toutes les news</a>
        </div>

    </section>

    <section class="mediation">
        <div class="title__container">
            <h2 class="title">Médiation</h2>
        </div>
        <div class="mediation__container row mediation__slider swiper-container">
            <div class="swiper-wrapper">
                @foreach(MediationArchive::lastPosts() as $post)
                    <div class="swiper-slide">
                        @include('components.mediation-item', $post)
                    </div>
                @endforeach
                @foreach(MediationArchive::lastPosts() as $post)
                    <div class="swiper-slide">
                        @include('components.mediation-item', $post)
                    </div>
                @endforeach
                @foreach(MediationArchive::lastPosts() as $post)
                    <div class="swiper-slide">
                        @include('components.mediation-item', $post)
                    </div>
                @endforeach
            </div>
            <div class="arrows">
                <svg class="arrow arrow-reverse swiper-button-prev" xmlns="http://www.w3.org/2000/svg"
                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 512 640"
                     enable-background="new 0 0 512 512" xml:space="preserve"><polygon
                            points="169.473,0.014 86.07,83.275 259.109,256.02 86.07,428.739 169.473,511.986 425.93,256.02 "></polygon></svg>
            <svg class="arrow swiper-button-next" xmlns="http://www.w3.org/2000/svg"
                 xmlns:xlink="http://www.w3.org/1999/xlink"
                 version="1.1" x="0px" y="0px" viewBox="0 0 512 640" enable-background="new 0 0 512 512"
                 xml:space="preserve"><polygon
                        points="169.473,0.014 86.07,83.275 259.109,256.02 86.07,428.739 169.473,511.986 425.93,256.02 "></polygon></svg>
            </div>
        </div>
        <div class="row button__container">
            <a class="button button--big" href="{{  get_post_type_archive_link('mediation') }}" title="Mediation">Voir
                toutes les visites</a>
        </div>
    </section>

@endsection
