{{--
  Template Name: La Conciergerie Template
--}}

@extends('layouts.app')

@section('content')
    @while(have_posts()) @php the_post() @endphp
    @include('partials.page-header')

    <div class="row content">
        @php the_content() @endphp
    </div>

    <div class="title__container">
        <h2 class="subtitle">L'équipe</h2>
    </div>
    <div class="row team js-slider--three slider__three swiper-container">
        <div class="swiper-wrapper">
            @foreach($team as $member)
                <div class="item swiper-slide">
                    <div class="item__link">
                        <img src="{{ $member->photo->sizes->medium_large }}" alt="{{ $member->name }}"/>
                        <div class="item__main">
                            <h2 class="item__title">{{ $member->name }}</h2>
                            <p class="item__subtitle">{{ $member->role }}</p>
                        </div>
                    </div>
                    <a href="/contact" class="button">Contacter</a>
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
    <div class="title__container">
        <h2 class="subtitle">Nos partenaires</h2>
    </div>
    <div class="row partners">
        @foreach($partners as $partner)
            <div class="partner">
                @if($partner->website)
                    <a target="_blank" href="{{ $partner->website }}">
                        <img src="{{ $partner->logo->url }}" alt="{{ $partner->name }}"/>
                    </a>
                @else
                    <img src="{{ $partner->logo->url }}" alt="{{ $partner->name }}"/>
                @endif

            </div>
        @endforeach
    </div>
    @endwhile
@endsection
