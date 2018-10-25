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
    @foreach($gallery_plans as $plan)
        <a href="{{ $plan->file->url }}" download>{{ $plan->file->title }}</a>
    @endforeach

    @foreach($press_pack as $pack)
        <a href="{{ $pack->file->url }}" download>{{ $pack->file->title }}</a>
    @endforeach
    @endwhile
@endsection
