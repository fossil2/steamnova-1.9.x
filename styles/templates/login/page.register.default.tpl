{block name="title" prepend}{$LNG.siteTitleRegister}{/block}
{block name="content"}

<script>
function registerSubmit(activeRecaptcha, use_recaptcha_on_register, referralID){
    var recaptchaResponse = false;

    if (activeRecaptcha == 1 && use_recaptcha_on_register == 1) {
        recaptchaResponse = grecaptcha.getResponse();
    }

    $.ajax({
        type: "POST",
        url: 'index.php?page=register&mode=send&ajax=1',
        data: {
            userName: $("#username").val(),
            password: $("#password").val(),
            email: $("#email").val(),
            secretQuestion: $("#secretQuestion").val(),
            secretQuestionAnswer: $('#secretQuestionAnswer').val(),
            language: $('#language option:selected').val(),
            rules: $('#rules').is(':checked'),
            referralID : $('#referralID').val(),
            g_recaptcha_response: recaptchaResponse,
            csrfToken: $('#csrfToken').val(),
        },
        success: function(data){
            var dataParsed = jQuery.parseJSON(data);
            $('.alert').remove();

            if (dataParsed.status == 'fail') {
                if (activeRecaptcha == 1 && use_recaptcha_on_register == 1) {
                    grecaptcha.reset();
                }

                $.each(dataParsed, function(typeError, errorText){
                    if (typeError == 'status') return;
                    $('#registerButton').before("<span class='alert alert-danger fs-6 py-1 my-1'>"+ errorText +"</span>");
                });

            } else if (dataParsed.status == 'success') {
                $('#registerButton').before("<span class='alert alert-success fs-6 py-1 my-1'>"+ dataParsed.successMessage +"</span>");

            } else if (dataParsed.status == 'redirect') {
                location.href = dataParsed.url;
            }
        }
    });
}
</script>

<!-- ========================================= -->
<!-- ?? U700 REGISTER – ZENTRAL MIT BUILT-IN STYLE -->
<!-- ========================================= -->

<div class="u700-register-wrapper">

    <div class="u700-register-panel">

        <h1 class="u700-register-title">{$LNG.siteTitleRegister}</h1>

        <form id="registerForm" action="index.php?page=register" method="post">

            <input id="csrfToken" type="hidden" name="csrfToken" value="{$csrfToken}">
            <input type="hidden" value="send" name="mode">
            <input type="hidden" value="{$externalAuth.account}" name="externalAuth[account]">
            <input type="hidden" value="{$externalAuth.method}" name="externalAuth[method]">
            <input id="referralID" type="hidden" value="{$referralData.id}" name="referralID">

            <!-- Universe -->
            <label>{$LNG.universe}</label>
            <select class="u700-input" name="uni" id="universe">
                {html_options options=$universeSelect selected=$UNI}
            </select>

            <!-- Facebook Login Hinweis -->
            {if !empty($externalAuth.account) && $facebookEnable}
                <label>{$LNG.registerFacebookAccount}</label>
                <span class="text fbname">{$accountName}</span>
            {/if}

            <!-- Username -->
            <label for="username">{$LNG.registerUsername}</label>
            <input id="username" type="text" name="username" class="u700-input">
            {if !empty($error.username)}<span class="error errorUsername"></span>{/if}
            <small class="u700-note">{$LNG.registerUsernameDesc}</small>

            <!-- Password -->
            <label for="password">{$LNG.registerPassword}</label>
            <input id="password" type="password" name="password" class="u700-input">
            {if !empty($error.password)}<span class="error errorPassword"></span>{/if}
            <small class="u700-note">{$registerPasswordDesc}</small>

            <!-- Email -->
            <label for="email">{$LNG.registerEmail}</label>
            <input id="email" type="email" name="email" class="u700-input">
            {if !empty($error.email)}<span class="error errorEmail"></span>{/if}
            <small class="u700-note">{$LNG.registerEmailDesc}</small>

            <!-- Sicherheitsfrage -->
            <label for="secretQuestion">{$LNG.registerSecretQuestionText}</label>
            <select id="secretQuestion" name="secretQuestion" class="u700-input">
                {foreach $LNG.registerSecretQuestionArray as $id => $currentQuestion}
                    <option value="{$id}">{$currentQuestion}</option>
                {/foreach}
            </select>

            <label for="secretQuestionAnswer">{$LNG.registerSecretQuestionAnswerText}</label>
            <input id="secretQuestionAnswer" type="text" name="secretQuestionAnswer" class="u700-input">

            <!-- Sprache -->
            {if count($languages) > 1}
                <label for="language">{$LNG.registerLanguage}</label>
                <select id="language" name="lang" class="u700-input">
                    {html_options options=$languages selected=$lang}
                </select>
                {if !empty($error.language)}<span class="error errorLanguage"></span>{/if}
            {/if}

            {if !empty($referralData.name)}
                <label>{$LNG.registerReferral}</label>
                <span class="text">{$referralData.name}</span>
            {/if}

            <!-- ReCaptcha -->
            {if $recaptchaEnable && $use_recaptcha_on_register}
                <label>{$LNG.registerCaptcha}</label>
                <div class="g-recaptcha" data-sitekey="{$recaptchaPublicKey}"></div>
            {/if}

            <!-- Regeln -->
            <label class="remember">
                <input type="checkbox" name="rules" id="rules">
                {$registerRulesDesc}
            </label>
            {if !empty($error.rules)}<span class="error errorRules"></span>{/if}

            <!-- Button -->
            <button id="registerButton" type="button"
                    onclick="registerSubmit('{$recaptchaEnable}','{$use_recaptcha_on_register}','{$referralData.id}');"
                    class="u700-btn u700-btn-full">
                {$LNG.buttonRegister}
            </button>

        </form>

    </div>
</div>

{/block}

{block name="script" append}
    {if $recaptchaEnable && $use_recaptcha_on_register}
        <script src="https://www.google.com/recaptcha/api.js?hl={$lang}"></script>
    {/if}
    <script src="./scripts/base/avoid_submit_on_refresh.js"></script>
{/block}
