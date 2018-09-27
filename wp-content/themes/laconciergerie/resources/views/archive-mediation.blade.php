@extends('layouts.app')

@section('content')
    @include('partials.page-header')

    @foreach(\App\Controllers\MediationArchive::lastPosts(-1) as $post)
        @include('components.mediation-item', $post)
    @endforeach

    {!! get_the_posts_navigation() !!}
@endsection
