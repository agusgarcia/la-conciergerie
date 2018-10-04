<header class="banner">
    <a class="brand" href="{{ home_url('/') }}">
        <img class="logo" src="@asset('images/logo_conciergerie.jpg')" alt="Logo La Conciergerie"/>
    </a>
    <button class="hamburger hamburger--spin" type="button">
        <span class="hamburger-box">
            <span class="hamburger-inner"></span>
        </span>
        {{--<span class="hamburger-label">Menu</span>--}}
    </button>
    <nav class="nav-primary">
        @if (has_nav_menu('primary_navigation'))
            {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
        @endif
    </nav>
</header>
