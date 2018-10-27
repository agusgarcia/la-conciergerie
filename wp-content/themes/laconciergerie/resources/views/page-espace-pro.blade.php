{{--
  Template Name: Professional Area Template
--}}

@extends('layouts.app')

@section('content')
    @while(have_posts()) @php the_post() @endphp
    @include('partials.page-header')
    <div class="row content">
        @php the_content() @endphp
    </div>
    <div class="row resources_list">
        <h2>Plans et vues de la salle</h2>
        <ul>
            @foreach($gallery_plans as $plan)
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                         x="0px"
                         y="0px" viewBox="0 0 36 45" enable-background="new 0 0 36 36" xml:space="preserve">
                <rect x="7.3225" y="29.2943" fill="#000000" width="21.355" height="3.8289"></rect>
                        <polygon fill="#000000"
                                 points="27.4745,18.114 24.7671,15.4065 19.9145,20.2592 19.9145,4.15 16.0855,4.15 16.0855,20.2592   11.2329,15.4065 8.5255,18.114 15.2925,24.8811 15.2911,24.8825 17.9985,27.5899 18,27.5885 18.0014,27.5899 20.7089,24.8825   20.7074,24.8811 "></polygon>
            </svg>
                    <a href="{{ $plan->file->url }}" download>{{ $plan->file->title }}</a>
                </li>
            @endforeach
        </ul>
    </div>
    @if($press_pack)
        <div class="row resources_list">
            <h2>Téléchargements</h2>
            <ul>
                @foreach($press_pack as $pack)
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                             x="0px"
                             y="0px" viewBox="0 0 36 45" enable-background="new 0 0 36 36" xml:space="preserve">
                <rect x="7.3225" y="29.2943" fill="#000000" width="21.355" height="3.8289"></rect>
                            <polygon fill="#000000"
                                     points="27.4745,18.114 24.7671,15.4065 19.9145,20.2592 19.9145,4.15 16.0855,4.15 16.0855,20.2592   11.2329,15.4065 8.5255,18.114 15.2925,24.8811 15.2911,24.8825 17.9985,27.5899 18,27.5885 18.0014,27.5899 20.7089,24.8825   20.7074,24.8811 "></polygon>
            </svg>
                        <a href="{{ $pack->file->url }}" download>{{ $pack->file->title }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    @endwhile
@endsection
