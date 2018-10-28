<header class="banner">
    <a class="brand" href="{{ home_url('/') }}">
       {{-- <img class="logo" src="@asset('images/logo_conciergerie.jpg')" alt="Logo La Conciergerie"/>--}}
        <svg class="logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 638.99 36"><defs><style>.cls-1{fill:#fff;}</style></defs><g id="Logo_conciergerie" data-name="Logo conciergerie"><path class="cls-1" d="M329.71,0V36l-18-18-3.52-3.52V36h-6V0l18,18,3.52,3.52V0ZM385.1,36V0h-6V36Zm214.37,0V0h-6V36ZM114,30V0h-6V36h27.52V30ZM274.85,6a12,12,0,1,0,12,12,12,12,0,0,0-12-12m0-6a18,18,0,1,1-18,18,18,18,0,0,1,18-18ZM243.34,26.49a12,12,0,1,1,0-17l4.24-4.24a18,18,0,1,0,0,25.46Zm122.19,0a12,12,0,1,1,0-17l4.24-4.24a18,18,0,1,0,0,25.46ZM171.15,30H154.56l-3,6h-6.71l18-36,18,36h-6.7Zm-3-6-5.3-10.58L157.56,24ZM456.58,36l-6-12h-8.81V36h-6V0h15.52a12,12,0,0,1,5.36,22.72L463.29,36ZM441.77,18h9.52a6,6,0,0,0,0-12h-9.52ZM577.43,36l-6-12h-8.81V36h-6V0h15.52a12,12,0,0,1,5.36,22.72L584.14,36ZM562.62,18h9.52a6,6,0,0,0,0-12h-9.52Zm-54.25-3H490.62v6h11.61A12,12,0,1,1,499.1,9.52l4.25-4.25A18,18,0,1,0,508.62,18,18.59,18.59,0,0,0,508.37,15ZM397.1,36h27.52V30H403.1V21h15.52V15H403.1V6h21.52V0H397.1ZM518,36h27.52V30H524V21h15.52V15H524V6h21.52V0H518Zm93.52,0H639V30H617.47V21H633V15H617.47V6H639V0H611.47ZM0,27v9H72V27L36,0Z"></path></g></svg>
    </a>
    <button class="hamburger hamburger--spin" type="button">
        <span class="hamburger-box">
            <span class="hamburger-inner"></span>
        </span>
        <span class="hamburger-label">Menu</span>
    </button>
    <nav class="nav-primary">
        @if (has_nav_menu('primary_navigation'))
            {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
        @endif
    </nav>
</header>
