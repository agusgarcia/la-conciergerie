@extends('layouts.app')

@section('content')
    @include('partials.page-header')

    @if (!have_posts())
        <div class="row content">
            <div class="alert alert-warning">
                {{ __('Sorry, but the page you were trying to view does not exist.', 'sage') }}
            </div>

        </div>
        <div class="button__container row">
            <a href="/" class="button button--big">Revenir à l'accueil</a>
        </div>
    @endif
@endsection
