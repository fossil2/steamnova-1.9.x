{block name="title" prepend}{$LNG.siteTitleIndex}{/block}
{block name="content"}

<script type="text/javascript">
function loginSubmit(activeRecaptcha,use_recaptcha_on_login){
    var recaptchaResponse = false;

    if (activeRecaptcha == 1 && use_recaptcha_on_login == 1) {
        recaptchaResponse = grecaptcha.getResponse();
    }

    $.ajax({
        type: "POST",
        url: 'index.php?page=login&mode=validate&ajax=1',
        data: {
            userEmail: $("#userEmail").val(),
            password: $("#password").val(),
            g_recaptcha_response: recaptchaResponse,
            csrfToken: $('#csrfToken').val(),
            remember_me : $('#remember_me').is(':checked'),
            universe : $('#universe option:selected').val(),
            rememberedTokenValidator : $('#rememberedTokenValidator').val(),
            rememberedTokenSelector : $('#rememberedTokenSelector').val(),
            rememberedEmail : $('#rememberedEmail').val()
        },
        success: function(data) {
            var dataParsed = jQuery.parseJSON(data);
            $('.alert').remove();

            if (dataParsed.status == 'fail') {
                if (activeRecaptcha == 1 && use_recaptcha_on_login == 1) {
                    grecaptcha.reset();
                }

                $.each(dataParsed, function(typeError, errorText) {
                    if (typeError == 'status') return;
                    $('#loginButton').before(
                        "<span class='alert alert-danger fs-6 py-1 my-1'>"+ errorText +"</span>"
                    );
                });

            } else if (dataParsed.status == 'redirect') {
                location.href = "game.php";
            }
        }
    });
}
</script>


<!-- ============================= -->
<!-- 🔵 NEUES U700 LOGIN LAYOUT   -->
<!-- ============================= -->

<div class="u700-wrapper">

    <!-- ============================= -->
    <!-- 🔵 LINKES PANEL (INFO)       -->
    <!-- ============================= -->
    <div class="u700-panel u700-left">
        <h1 class="u700-title">{sprintf($LNG.loginWelcome, $gameName)}</h1>
        <h2 class="u700-sub">{$LNG.login1}</h2>

        <p class="u700-desc">
            {sprintf($LNG.loginServerDesc, $gameName)}
        </p>

        <a href="index.php?page=register" class="u700-btn">
            {$LNG.buttonRegister}
        </a>
    </div>


    <!-- ============================= -->
    <!-- 🔵 RECHTES PANEL (LOGIN)     -->
    <!-- ============================= -->
    <div class="u700-panel u700-right">

        <h2>{$LNG.loginHeader}</h2>

        <form id="login" action="" method="post">

            <input type="hidden" id="csrfToken" name="csrfToken" value="{$csrfToken}">
            <input type="hidden" id="rememberedEmail" name="rememberedEmail" value="{$rememberedEmail}">
            <input type="hidden" id="rememberedTokenSelector" name="rememberedTokenSelector" value="{$rememberedTokenSelector}">
            <input type="hidden" id="rememberedTokenValidator" name="rememberedTokenValidator" value="{$rememberedTokenValidator}">

            <select class="u700-input" name="uni" id="universe">
                {foreach $universeSelect as $universeID => $currentUniverse}
                    <option value="{$universeID}" {if $currentUniverse == $rememberedUniverseID}selected{/if}>
                        {$currentUniverse}
                    </option>
                {/foreach}
            </select>

            <input class="u700-input" id="userEmail" type="text"
                   name="userEmail" placeholder="{$LNG.login_email}"
                   value="{if $rememberedEmail}{$rememberedEmail}{/if}">

            <input class="u700-input" id="password" type="password"
                   name="password" placeholder="{$LNG.loginPassword}"
                   value="{if $rememberedPassword}password{/if}">

            {if $recaptchaEnable && $use_recaptcha_on_login}
                <div class="g-recaptcha" data-sitekey="{$recaptchaPublicKey}"></div>
            {/if}

            <label class="remember">
                <input id="remember_me" type="checkbox" name="remember_me"
                       {if $rememberedPassword}checked{/if}>
                <span>Remember me</span>
            </label>

            <button id="loginButton"
                    class="u700-btn u700-btn-full"
                    type="button"
                    onclick="loginSubmit('{$recaptchaEnable}','{$use_recaptcha_on_login}');">
                {$LNG.loginButton}
            </button>

        </form>

        <a class="u700-btn-mini" href="index.php?page=lostPassword">
            🔑 {$LNG.buttonLostPassword}
        </a>

        <span class="u700-note">{$loginInfo}</span>
    </div>

</div>

{/block}

{if $recaptchaEnable && $use_recaptcha_on_login}
    {block name="script" append}
        <script src="https://www.google.com/recaptcha/api.js?hl=de"></script>
    {/block}
{/if}
