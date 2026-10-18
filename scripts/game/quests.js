/**
 * SteamNova Quest System
 * Bereinigte / modernisierte Version
 */

(function ($) {
    'use strict';

    let questVerifyTimer = null;

    /**
     * Übersetzung aus translation.js.php holen.
     * Fallback nur für den Fall, dass der Eintrag noch fehlt.
     */
    function getQuestTranslation(key, fallback) {
        if (
            typeof window.questTranslations !== 'undefined' &&
            typeof window.questTranslations[key] !== 'undefined'
        ) {
            return window.questTranslations[key];
        }

        return fallback;
    }

    /**
     * Quest-Seite initialisieren.
     */
    function initQuests() {
        const onglets = document.getElementById('onglets');

        if (!onglets) {
            return;
        }

        const liOnglet = onglets.getElementsByTagName('li');

        if (liOnglet.length === 0) {
            return;
        }

        /**
         * Erste Kategorie aktivieren.
         */
        liOnglet[0].classList.add('actif');

        /**
         * Nicht blind Kategorie 1 laden,
         * sondern den Wert des ersten vorhandenen Eintrags.
         */
        const firstCategoryId = liOnglet[0].value;

        if (firstCategoryId) {
            questLists(firstCategoryId);
        }

        /**
         * Kategorien anklickbar machen.
         */
        for (let i = 0; i < liOnglet.length; i++) {

            liOnglet[i].addEventListener('click', function () {

                for (let j = 0; j < liOnglet.length; j++) {
                    liOnglet[j].classList.remove('actif');
                }

                this.classList.add('actif');

                const idCat = this.value;

                if (idCat) {
                    questLists(idCat);
                }
            });
        }

        /**
         * Quest-Fortschritt starten.
         */
        verifQuest();
    }


    /**
     * Quests einer Kategorie laden.
     */
    window.questLists = function (idCat) {

        $.ajax({
            type: 'GET',
            url: 'game.php?page=questsAjax',

            data: {
                categorie_id: idCat
            },

            dataType: 'json',

            success: function (data) {

                const contents = $('#contents');

                /**
                 * Alten Inhalt vollständig entfernen.
                 */
                contents.empty();

                /**
                 * Neue Quest-Ausgabe einsetzen.
                 */
                contents.append(data);
            },

            error: function (xhr) {
                console.error(
                    'Quest-Liste konnte nicht geladen werden:',
                    xhr.responseText
                );
            }
        });
    };


    /**
     * Kleine Benachrichtigung anzeigen.
     */
    window.NotifyBoxAdm = function (colorAlert, text) {

        const tip = $('#tooltip');

        if (!tip.length) {
            return;
        }

        tip
            .stop(true, true)
            .html(text)
            .removeClass(
                'danger warning success info'
            )
            .addClass('notify')
            .addClass(colorAlert);

        /**
         * Tooltip mittig im Spielbereich positionieren.
         */
        const leftMenuWidth = $('#leftmenu').outerWidth() || 0;

        const leftPosition =
            (
                ($(window).width() - leftMenuWidth) / 2
            )
            - (tip.outerWidth() / 2)
            + leftMenuWidth;

        tip.css({
            left: leftPosition
        });

        tip.show();

        window.setTimeout(function () {

            tip.fadeOut(
                1000,
                function () {
                    tip.removeClass(
                        'notify danger warning success info'
                    );
                }
            );

        }, 2500);
    };


    /**
     * Quest annehmen / starten.
     */
    window.questConfirm = function (idQuest) {

        if (!idQuest) {
            return;
        }

        const button = $('#btn_quest_' + idQuest);

        /**
         * Mehrfachklick verhindern.
         */
        button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: 'game.php?page=questsAjax&mode=runQuest',

            data: {
                quest_id: idQuest
            },

            dataType: 'json',

            success: function (data) {

                NotifyBoxAdm(
                    data.error,
                    data.message
                );

                /**
                 * Nur bei erfolgreichem Start
                 * die Quest-Anzeige ändern.
                 */
                if (
                    data.error === 'success' &&
                    data.questID
                ) {

                    const questId = data.questID;

                    const badge =
                        $('#quest_badge_' + questId);

                    badge
                        .removeClass(
                            'danger warning success info'
                        )
                        .addClass(data.badgeStyle)
                        .text(data.badgeContent);

                    /**
                     * Start-Button entfernen.
                     * Der Fortschrittsbalken wird anschließend
                     * durch verifQuest() erzeugt.
                     */
                    $('#quest_button_' + questId)
                        .empty()
                        .addClass(
                            'progresse_bar_border'
                        );

                    /**
                     * Fortschritt sofort neu prüfen.
                     */
                    checkQuestProgress();
                } else {
                    button.prop('disabled', false);
                }
            },

            error: function (xhr) {

                button.prop('disabled', false);

                console.error(
                    'Quest konnte nicht gestartet werden:',
                    xhr.responseText
                );
            }
        });
    };


    /**
     * Quest-Fortschritt einmalig abfragen.
     */
    function checkQuestProgress() {

        $.ajax({
            type: 'GET',
            url: 'game.php?page=questsAjax&mode=verifQuest',

            dataType: 'json',

            success: function (data) {

                $.each(
                    data,
                    function (key, value) {

                        const questButton =
                            $('#quest_button_' + key);

                        if (!questButton.length) {
                            return;
                        }

                        /**
                         * Vorherige dynamische Elemente entfernen.
                         */
                        $('#btn_quest_' + key).remove();
                        $('#progresse_bar_' + key).remove();

                        /**
                         * Bereits abgeschlossene Quest:
                         * nichts mehr anzeigen.
                         */
                        if (
                            parseInt(
                                value.user_quest_users_finish,
                                10
                            ) >= 2
                        ) {
                            questButton
                                .removeClass(
                                    'progresse_bar_border'
                                )
                                .empty();

                            return;
                        }

                        /**
                         * Quest-Ziel erreicht:
                         * Abschluss-Button anzeigen.
                         */
                        if (
                            value.user_quest_finish === true
                        ) {

                            questButton
                                .removeClass(
                                    'progresse_bar_border'
                                )
                                .empty();

                            const finishText =
                                getQuestTranslation(
                                    'finish',
                                    'Fertig'
                                );

                            $('<button>', {
                                id: 'btn_quest_' + key,
                                type: 'button',
                                text: finishText,
                                class: 'quest_finish_button'
                            })
                                .on(
                                    'click',
                                    function () {
                                        questFinish(key);
                                    }
                                )
                                .appendTo(questButton);

                            return;
                        }

                        /**
                         * Fortschritt anzeigen.
                         */
                        let percent = parseFloat(
                            value.count_current_pourcent
                        );

                        if (isNaN(percent)) {
                            percent = 0;
                        }

                        if (percent < 0) {
                            percent = 0;
                        }

                        if (percent > 100) {
                            percent = 100;
                        }

                        let progressClass = 'progress-low';

                        if (percent >= 80) {
                            progressClass = 'progress-high';

                        } else if (percent >= 50) {
                            progressClass = 'progress-medium';
                        }

                        questButton
                            .addClass(
                                'progresse_bar_border'
                            )
                            .empty();

                        $('<div>', {
                            id: 'progresse_bar_' + key,
                            class:
                                'progresseBar ' +
                                progressClass
                        })
                            .css({
                                width: percent + '%'
                            })
                            .append(
                                $('<span>').text(
                                    percent + '%'
                                )
                            )
                            .appendTo(questButton);
                    }
                );
            },

            error: function (xhr) {

                console.error(
                    'Quest-Fortschritt konnte nicht geprüft werden:',
                    xhr.responseText
                );
            }
        });
    }


    /**
     * Quest-Fortschritt regelmäßig prüfen.
     *
     * Alt: jede Sekunde.
     * Neu: alle 5 Sekunden.
     *
     * Das reicht für Quests völlig aus und reduziert
     * unnötige AJAX-/Datenbankanfragen deutlich.
     */
    window.verifQuest = function () {

        if (questVerifyTimer !== null) {
            window.clearTimeout(
                questVerifyTimer
            );
        }

        checkQuestProgress();

        questVerifyTimer = window.setTimeout(
            verifQuest,
            5000
        );
    };


    /**
     * Quest abschließen und Belohnung holen.
     */
    window.questFinish = function (idQuest) {

        if (!idQuest) {
            return;
        }

        const button =
            $('#btn_quest_' + idQuest);

        button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: 'game.php?page=questsAjax&mode=finishQuest',

            data: {
                quest_id: idQuest
            },

            dataType: 'json',

            success: function (data) {

                NotifyBoxAdm(
                    data.error,
                    data.message
                );

                /**
                 * Nur bei erfolgreichem Abschluss
                 * Oberfläche verändern.
                 */
                if (
                    data.error === 'success' &&
                    data.questID
                ) {

                    const questId = data.questID;

                    $('#quest_badge_' + questId)
                        .removeClass(
                            'danger warning success info'
                        )
                        .addClass(data.badgeStyle)
                        .text(data.badgeContent);

                    $('#quest_button_' + questId)
                        .remove();

                } else {
                    button.prop(
                        'disabled',
                        false
                    );
                }
            },

            error: function (xhr) {

                button.prop(
                    'disabled',
                    false
                );

                console.error(
                    'Quest konnte nicht abgeschlossen werden:',
                    xhr.responseText
                );
            }
        });
    };


    /**
     * Nach dem Laden der Seite starten.
     */
    $(document).ready(function () {
        initQuests();
    });

})(jQuery);