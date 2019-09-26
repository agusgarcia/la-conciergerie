<div class="event">
    <img class="hero" src="{{ $main_image->url }}" alt="{{ $main_image->alt }}" width="250"/>
    {{--<h1>{{ $event_name }}</h1>--}}
    <div class="row">
        <h1 class="title">{{ $event_title }}</h1>
        <h2 class="subtitle">                        {{ App::formattedDate($opening_date) }}
        </h2>
    </div>

    <div class="content">
        <div class="content__first">
            <div>
                <div class="text text--main">
                    {!! $main_text !!}
                </div>
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
                        <strong>Date de l'événement</strong>
                        <p>{{  App::formattedDate($opening_date) }}</p>
                    </li>
                    <li>
                        <strong>Heure de l'événement</strong>
                        <p>{{ $event_hour }}</p>
                    </li>
                    @if($other_dates->other_date)
                        <li>
                            <strong>{{ $other_dates->other_date_title }}</strong>
                            <p>{{ $other_dates->other_date }}</p>
                        </li>
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
@include('partials.single-navigation')