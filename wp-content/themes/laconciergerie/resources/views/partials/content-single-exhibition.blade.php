<div class="exhibition">

    <img class="hero" src="{{ $main_image->url }}" alt="{{ $main_image->alt }}"/>
    <div class="row">
        <h1 class="title">{{ $artist_name }}</h1>
        <h2 class="subtitle">{{ $exhibition_title }}</h2>
    </div>
    <div class="content__first">
        <div>
            <div class="text text--main">{!! $main_text !!}</div>
            <div class="media__container">
                @if($main_media[0]->acf_fc_layout == 'image')
                    <figure>
                        <img src="{{ $main_media[0]->main_image->url }}" alt="{{ $main_media[0]->main_image->alt }}"
                             width="250"/>
                        <figcaption>{{ $main_media[0]->main_image->caption }}</figcaption>
                    </figure>
                @else
                    {!! $main_media[0]->main_video !!}
                @endif
            </div>
        </div>
    </div>
    <div class="content__second">
        <div>
            <div class="media__container">
                @if($main_media[1]->acf_fc_layout == 'image')
                    <figure>
                        <img src="{{ $main_media[1]->main_image->url }}"
                             alt="{{ $main_media[1]->main_image->alt }}"/>
                        <figcaption>{{ $main_media[1]->main_image->caption }}</figcaption>
                    </figure>
                @else
                    {!! $main_media[1]->main_video !!}
                @endif
            </div>
            <ul class="information">
                <li>
                    <strong>Vernissage</strong>
                    <p>{{ App::formattedDate($opening_date) }}</p>
                </li>
                <li>
                    <strong>Exposition</strong>
                    <p>Du {{ $exhibition_date->exhibition_date_opening }}
                        au {{ App::formattedDate($exhibition_date->exhibition_date_closing) }}</p>
                    <p>{{ $exhibition_closing_dates }}</p>
                </li>
                <li>
                    <strong>{{ $other_dates->other_date_title }}</strong>
                    <p>{{ $other_dates->other_date }}</p>
                </li>
                <li>
                    <strong>Site web</strong>
                    <a target="_blank" href="{{ $website }}">{{ $website }}</a>
                </li>
                @if($documents)
                    @foreach($documents as $document)
                        <li>
                            <strong>{{ $document->document_title }}</strong>
                            <p><a href="{{ $document->document->url }}" download>Télécharger</a></p>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
    <div class="text text--secondary row">{!! $secondary_text !!}</div>
    @if($images)
        <div class="gallery">
            @foreach($images as $image)
                <figure>
                    <div class="image__container">
                        <img src="{{ $image->sizes->large }}" alt="{{ $image->alt }}">
                    </div>
                    <figcaption>{{ $image->caption }}</figcaption>
                </figure>
            @endforeach
        </div>
    @endif
    @if($slider->slider_images)
        <div class="gallery__slider swiper-container row">
            <h3 class="title">{{ $slider->slider_title }}</h3>
            <div class="swiper-wrapper">
                @foreach($slider->slider_images as $image)
                    <div class="swiper-slide">
                        <figure>
                            <div class="image__container">
                                <img src="{{ $image->sizes->large }}" alt="{{ $image->alt }}">
                            </div>
                            <figcaption>{{ $image->caption }}</figcaption>
                        </figure>
                    </div>
                @endforeach
            </div>
            <div class="arrows arrows--big">
                <svg class="arrow arrow--reverse swiper-button-prev" xmlns="http://www.w3.org/2000/svg"
                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 125">
                    <g transform="translate(0,-952.36218)">
                        <path d="m 49.99997,1018.5184 2.59376,-2.2188 28,-23.99996 -5.1875,-6.0937 -25.40626,21.78116 -25.4062,-21.78116 -5.1875,6.0937 28,23.99996 2.5937,2.2188 z"
                              stroke="none" marker="none" visibility="visible"
                              display="inline"
                              overflow="visible"></path>
                    </g>
                </svg>
                <svg class="arrow swiper-button-next" xmlns="http://www.w3.org/2000/svg"
                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 125">
                    <g transform="translate(0,-952.36218)">
                        <path d="m 49.99997,1018.5184 2.59376,-2.2188 28,-23.99996 -5.1875,-6.0937 -25.40626,21.78116 -25.4062,-21.78116 -5.1875,6.0937 28,23.99996 2.5937,2.2188 z"
                              stroke="none" marker="none" visibility="visible"
                              display="inline"
                              overflow="visible"></path>
                    </g>
                </svg>
            </div>
        </div>
    @endif
</div>

@php($posts = $adjacent_posts)
@php($prevPost = $posts[0])
@php($nextPost = $posts[1])
<div class="post__navigation">
    @if($prevPost)
        <a class="post__previous" style="--page-color:{{ get_post($prevPost)->color }}"
           href="{{ get_post_permalink($prevPost) }}"> {{ get_post($prevPost)->post_title }} <span>Avant</span></a>
    @endif

    @if($nextPost)
        <a class="post__next" style="--page-color:{{  get_post($nextPost)->color }}"
           href="{{ get_post_permalink($nextPost) }}">{{ get_post($nextPost)->post_title }} <span>Après</span> </a>
    @endif
</div>