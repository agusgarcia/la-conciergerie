<footer>
    <img class="logo" src="@asset('images/logo_conciergerie.jpg')" alt="Logo La Conciergerie"/>

    <div class="footer__information">
        @php dynamic_sidebar('sidebar-information') @endphp
    </div>
    {{-- Main menu & secondary menu--}}
    <div class="footer__menu footer__menu--main ">
        @php dynamic_sidebar('sidebar-primary') @endphp
    </div>
    <div class="footer__menu footer__menu--secondary">
        @php dynamic_sidebar('sidebar-footer') @endphp
    </div>
    {{--{{ get_theme_mod( 'text_setting', '' ) }}--}}
    {{-- Newsletter form --}}
    <div class="footer__newsletter">
        <div id="mc_embed_signup">
            <form action="https://conciergerie-art.us18.list-manage.com/subscribe/post?u=baf0bffcb9544bd67b0638d8e&amp;id=ce4fc7a4c1"
                  method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
                  target="_blank" novalidate>
                <div id="mc_embed_signup_scroll">
                    <label for="mce-EMAIL">Inscrivez-vous à notre newsletter !</label>
                    <input type="email" value="" name="EMAIL" class="email input" id="mce-EMAIL"
                           placeholder="Votre adresse e-mail"
                           required>
                    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                    <div style="position: absolute; left: -5000px;" aria-hidden="true">
                        <input type="text" name="b_baf0bffcb9544bd67b0638d8e_ce4fc7a4c1" tabindex="-1" value="">
                    </div>
                    <input type="submit" value="Ok" name="subscribe"
                           id="mc-embedded-subscribe" class="button">
                </div>
            </form>
        </div>

        <!--End mc_embed_signup-->

    </div>

    {{-- Agustina --}}
    <div class="footer__credits">
        <a href="https://agusgarcia.com">
            <abbr title="Agustina fuit hic">
              <img src="@asset('images/signature_agus_white.png')" alt="Agustina Garcia"> </abbr>
        </a>
    </div>
</footer>
