@extends('layouts.app')

@section('content')
    @while(have_posts()) @php the_post() @endphp
    @include('partials.page-header')

    @php the_content() @endphp

    {!! $educational_resources->intro !!}

    @foreach($educational_resources->resources_list as $resource)
        ↓ <a href="{{ $resource->document->url }}" title="{{ $resource->document->title }}"
             download>{{ $resource->document->title }}</a>
    @endforeach

    @foreach($images as $image)
        <figure>
            <img src="{{ $image->sizes->thumbnail }}" alt="{{ $image->alt }}">
            <figcaption>{{ $image->caption }}</figcaption>
        </figure>
    @endforeach

    <article class="mediation">
        <h2>Mediation</h2>
        @foreach(\App\Controllers\MediationArchive::lastPosts() as $post)
            @include('components.mediation-item', $post)
        @endforeach
        <a href="#">Voir toutes les visites</a>
    </article>

    @endwhile
@endsection
