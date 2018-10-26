<!doctype html>
<html lang="{{ get_bloginfo('language') }}">
@include('partials.head')
<body>
<div id="transition-wrapper">
</div>
<div id="barba-wrapper">
    <div class="barba-container">
        <div id="body" @php body_class() @endphp data-color="{{ App::pageColor() }}">
            @php do_action('get_header') @endphp
            @include('partials.header')
            <div class="wrap container" role="document">
                <main class="main">
                    @yield('content')
                </main>
                @if (App\display_sidebar())
                    <aside class="sidebar">
                        @include('partials.sidebar')
                    </aside>
                @endif
            </div>
            @php do_action('get_footer') @endphp
            @include('partials.footer')
            @php wp_footer() @endphp
        </div>
    </div>
</div>
</body>
</html>
