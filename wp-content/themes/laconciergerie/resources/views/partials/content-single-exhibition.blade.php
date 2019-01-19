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
                    <p>{{ App::formattedDate($opening_date) }} à {{ $preview_hour }}</p>
                </li>
                <li>
                    <strong>Exposition</strong>
                    <p>Du {{ App::formattedDateNoYear($exhibition_date->exhibition_date_opening) }}
                        au {{ App::formattedDate($exhibition_date->exhibition_date_closing) }}</p>
                </li>
                @if($exhibition_date->exhibition_closing_dates)
                    <li>
                        <strong>Fermeture</strong>
                        <p>{{ $exhibition_date->exhibition_closing_dates }}</p>
                    </li>
                @endif
                @if($other_dates)
                    @foreach($other_dates as $date)
                        <li>
                            <strong>{{ $date->other_date_title }}</strong>
                            <p>{{ $date->other_date }}</p>
                        </li>
                    @endforeach
                @endif
                @if($website->website_link)
                    @if(empty($website->website_title))
                        @php($website_title = $website_link)
                    @endif
                    <li>
                        <strong>Site web</strong>
                        <a target="_blank" href="{{ $website->website_link }}">{{ $website->website_title }}</a>
                    </li>
                @endif
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
        @include('components.gallery-slider', $images)
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