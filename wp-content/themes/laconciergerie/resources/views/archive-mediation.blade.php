@extends('layouts.app')

@section('content')
    @include('partials.page-header')

    @foreach(\App\Controllers\MediationArchive::lastPosts(-1) as $post)
        @php($post['row'] = 'row')
        @include('components.mediation-item', $post)
    @endforeach
    <div class="post__navigation">

        @if(get_previous_posts_link())
            <div class="post__previous">
                {!! get_previous_posts_link('Articles précédents')  !!}
            </div>

        @endif
        @if(get_next_posts_link())
            <div class="post__next">
                {!! get_next_posts_link('Nouveaux articles')  !!}
            </div>
        @endif
    </div>
@endsection
