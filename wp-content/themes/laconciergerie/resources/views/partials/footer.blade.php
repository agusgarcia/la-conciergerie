<hr>
<footer style="display:flex;">
    <div class="container">
        <img src="@asset('images/logo_conciergerie.jpg')" alt="Logo La Conciergerie" width="100"/>
        <p>La conciergerie</p>
        <p>17, Montée St. Jean</p>
        <p>73290 La Motte-Servolex</p>
        <a href="https://facebook.com/laconciergerieartcontemporain"><img
                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c2/F_icon.svg/267px-F_icon.svg.png"
                    alt="Logo Facebook" width="50"/> Retrouvez-nous sur Facebook !</a>
        <p>Horaires d'ouverture</p>
        <p>Mercredi et vendredi : 15h - 18h</p>
        <p>Samedi : 10h - 13h</p>
        <p>Autres ouvertures sur rendez-vous</p>
    </div>
    <div>
        @php dynamic_sidebar('sidebar-footer') @endphp
    </div>
    <div>
        <div id="mc_embed_signup">
            <form action="https://conciergerie-art.us18.list-manage.com/subscribe/post?u=baf0bffcb9544bd67b0638d8e&amp;id=ce4fc7a4c1"
                  method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
                  target="_blank" novalidate>
                <div id="mc_embed_signup_scroll">
                    <label for="mce-EMAIL">Souscrivez-vous à notre newsletter !</label>
                    <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="Votre adresse e-mail"
                           required>
                    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text"
                                                                                              name="b_baf0bffcb9544bd67b0638d8e_ce4fc7a4c1"
                                                                                              tabindex="-1" value="">
                    </div>
                    <div class="clear"><input type="submit" value="Ok" name="subscribe"
                                              id="mc-embedded-subscribe" class="button"></div>
                </div>
            </form>
        </div>

        <!--End mc_embed_signup-->
    </div>

    <div style="background-color: #2b2b2b">
        <a href="https://agusgarcia.com">
            <img src="@asset('images/signature_agus_white.png')" alt="Agustina Garcia">
        </a>
    </div>
</footer>
