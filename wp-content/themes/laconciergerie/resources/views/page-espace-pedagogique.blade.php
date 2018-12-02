@extends('layouts.app')

@section('content')
    @while(have_posts()) @php the_post() @endphp
    @include('partials.page-header')
    <div class="row content">
        @php the_content() @endphp
    </div>

    @if($educational_resources)
        <div class="row resources_list">
            {!! $educational_resources->intro !!}

            <ul>
                @foreach($educational_resources->resources_list as $resource)
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                             x="0px"
                             y="0px" viewBox="0 0 36 45" enable-background="new 0 0 36 36" xml:space="preserve">
                <rect x="7.3225" y="29.2943" fill="#000000" width="21.355" height="3.8289"></rect>
                            <polygon fill="#000000"
                                     points="27.4745,18.114 24.7671,15.4065 19.9145,20.2592 19.9145,4.15 16.0855,4.15 16.0855,20.2592   11.2329,15.4065 8.5255,18.114 15.2925,24.8811 15.2911,24.8825 17.9985,27.5899 18,27.5885 18.0014,27.5899 20.7089,24.8825   20.7074,24.8811 "></polygon>
            </svg>
                        <a href="{{ $resource->document->url }}" title="{{ $resource->document->title }}"
                           download>{{ $resource->document->title }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row images">

        @foreach($images as $image)
            <figure>
                <img src="{{ $image->sizes->medium_large }}" alt="{{ $image->alt }}">
                @if($image->caption)
                    <figcaption>{{ $image->caption }}</figcaption>
                @endif
            </figure>
        @endforeach
    </div>

    @include('components.last-mediation-items')

    @endwhile
@endsection
