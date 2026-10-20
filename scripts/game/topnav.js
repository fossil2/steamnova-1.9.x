// topnav.js
// RealTimeRessisanzeige for 2Moons
// @version 1.0
// @copyright 2010 by ShadoX
/*
function resourceTicker(config, init) {
	if(typeof init !== "undefined" && init === true)
		window.setInterval(function(){resourceTicker(config)}, 1000);

	var element	= $('#'+config.valueElem);
	if(element.hasClass('res_current_max'))
	{
		return false;
	}

	var nrResource = Math.max(0, Math.floor(parseFloat(config.available) + parseFloat(config.production) / 3600 * (serverTime.getTime() - startTime) / 1000));

	if (nrResource < config.limit[1])
	{
		if (!element.hasClass('res_current_warn') && nrResource >= config.limit[1] * 0.9)
		{
			element.addClass('res_current_warn');
		}
		if(viewShortlyNumber) {
			element.attr('data-tooltip-content', NumberGetHumanReadable(nrResource));
			element.html(shortly_number(nrResource));
		} else {
			element.html(NumberGetHumanReadable(nrResource));
		}
	} else {
		element.addClass('res_current_max');
	}
}

function getRessource(name) {
	return parseInt($('#current_'+name).data('real'));
}
*/

// topnav.js
// RealTimeRessisanzeige for 2Moons
// @version 2.0
// @copyright 2026 by U700

function resourceTicker(config, init = false) {
    // Intervall-Logik mit Standardparameter und Arrow-Function
    if (init) {
        window.setInterval(() => resourceTicker(config), 1000);
    }

    const element = document.getElementById(config.valueElem);
    if (!element || element.classList.contains('res_current_max')) {
        return false;
    }

    // Berechnung (Nutzt globale Variablen 'serverTime' und 'startTime')
    const timeDiff = (serverTime.getTime() - startTime) / 1000;
    const productionPerSecond = parseFloat(config.production) / 3600;
    const currentResource = parseFloat(config.available) + productionPerSecond * timeDiff;
    const nrResource = Math.max(0, Math.floor(currentResource));

    // Limit-Prüfung und UI-Updates
    if (nrResource < config.limit[1]) {
        if (!element.classList.contains('res_current_warn') && nrResource >= config.limit[1] * 0.9) {
            element.classList.add('res_current_warn');
        }
        
        // Lokale Hilfsfunktionen (zuvor global angenommen)
        if (typeof viewShortlyNumber !== "undefined" && viewShortlyNumber) {
            element.setAttribute('data-tooltip-content', NumberGetHumanReadable(nrResource));
            element.innerHTML = shortly_number(nrResource);
        } else {
            element.innerHTML = NumberGetHumanReadable(nrResource);
        }
    } else {
        element.classList.add('res_current_max');
    }
}

function getRessource(name) {
    const element = document.getElementById(`current_${name}`);
    // Nutzen des nativen dataset statt jQuery .data()
    return element ? parseInt(element.dataset.real, 10) : 0;
}
