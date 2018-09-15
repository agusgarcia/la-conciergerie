@include('partials.page-header')
@include('partials.content-page')

<img src="{{ $main_image->url }}" alt="{{ $main_image->alt }}" width="250"/>
{{--<h1>{{ $event_name }}</h1>--}}
<h2>{{ $exhibition_title }}</h2>
<p>{!! $main_text !!}</p>
@foreach ($main_media as $media)
    @if($media->acf_fc_layout == 'image')
        <figure>
            <img src="{{ $media->main_image->url }}" alt="{{ $media->main_image->alt }}" width="250"/>
            <figcaption>{{ $media->main_image->caption }}</figcaption>
        </figure>
    @else
        {!! $media->main_video !!}
    @endif
@endforeach

<ul>
    <li>
        <strong>Date de l'événement</strong>
        <p>{{ $opening_date }}</p>
    </li>
    @if($other_dates->other_date)
        <li>
            <strong>{{ $other_dates->other_date_title }}</strong>
            <p>{{ $other_dates->other_date }}</p>
        </li>
    @endif
    @if($website)
        <li>
            <strong>Site web</strong>
            <a href="{{ $website }}">{{ $website }}</a>
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
<p>{!! $secondary_text !!}</p>

@if($images)
    @foreach($images as $image)
        <figure>
            <img src="{{ $image->sizes->thumbnail }}" alt="{{ $image->alt }}">
            <figcaption>{{ $image->caption }}</figcaption>
        </figure>

    @endforeach
@endif

@if($slider->slider_title)
    <h3>{{ $slider->slider_title }}</h3>
@endif
@if($slider->slider_images)
    @foreach($slider->slider_images as $image)
        @if($image->type === 'video')
            <figure>
                <video src="{{ $image->url }}" alt="{{ $image->alt }}" controls></video>
                <figcaption>{{ $image->caption }}</figcaption>
            </figure>
        @else
            {{-- TODO : Remove thumbnail size --}}
            <figure>
                <img src="{{ $image->sizes->thumbnail }}" alt="{{ $image->alt }}">
                <figcaption>{{ $image->caption }}</figcaption>
            </figure>
        @endif

    @endforeach
@endif

@php($posts = SingleExhibition::adjacentPosts())
@php($prevPost = $posts[0])
@php($nextPost = $posts[1])
@if($prevPost)
    <a href="{{ get_post_permalink($prevPost) }}"> << {{ get_post($prevPost)->post_title }}</a>
@endif
/
@if($nextPost)
    <a href="{{ get_post_permalink($nextPost) }}">{{ get_post($nextPost)->post_title }} >></a>
@endif