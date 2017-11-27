/*
 * Copyright (c) 2017 Hoogspanningsnet.com
 * http://netkaart.hoogspanningstnet.com
 *
 * This file contains some settings 
 */

// Set the cookie expire period in days
var cookieExpires = 30;

// Sets the common file dir relative path
var CommonFileDir = 'commonfiles/';

// Set the welcome message shown to new users
var WelcomeTitle = 'HoogspanningsNet Netkaart';
var WelcomeMessage = 
	'Welkom nieuwe gebruiker,<p>'+
	'Deze netkaart is een weergave van het hoogspanningsnet in Nederland, België en enkele gebieden daarbuiten.'+
	'Je kan in- en uitzoomen voor meer details van het net. Door op objecten te klikken of tikken kan je meer informatie krijgen van het betreffende object.<br>'+
	'Instellingen, de legenda en essentiële links zijn te vinden via de menuknoppen aan de linkerzijde van het scherm.<p>'+
	'<i>Veel plezier bij het gebruik van de netkaart</i><br><p>'+
	'<img height="31px" src="files/by-nc-nd.eu.png">&nbsp;<img height="31px" src="files/cc.primary.srr.gif"><img style="float:right" src="files/hsnet_transpa1.png">';

//Set the information message shown to users changes in the netkaart
var NewInfoTitle = 'Nieuw in de netkaart';
var NewInfoDate = '20-07-2017';
var NewInfoShowDays = 10;
var NewInfoMessage = 
	'<i>Datum: '+ NewInfoDate +'</i><p>'+
	'Sinds 20 juli is de Netkaart aangevuld met ca. 10.000 hoogspanningsmasten in voornamelijk het oostelijk deel van België. De mastinformatie wordt in de komende tijd verder aangevuld.<br>'+
	'<i>Veel plezier bij het gebruik van deze nieuwe mogelijkheid.</i>';

//Sets an alert message, alertbox will be shown when AlertMessage is not Empty
var NotAvailableMsg = 
	'<br>De webkaart is tijdelijk niet beschikbaar.<br>'+
	'Onze excuses voor het ongemak.<p>'+
	'<i>Netkaartteam Hoogspanningsnet.com</i>';
var NoAlert = '';
var AlertTitle = '<font color="red">MEDEDELING</font>';
var AlertMessage = NoAlert;

//========================== COOKIE FUNCTIONS =============================
//function to write cookie values
function writeCookie(name, value) {
	var now = new Date();
	var time = now.getTime();
	time += cookieExpires * 24 * 3600 * 1000;
	now.setTime(time);
//	console.log('netkaart-' +name+ '=' +value+ ';expires=' +now.toUTCString()+ ';');
	document.cookie = 'netkaart-' +name+ '=' +value+ ';expires=' +now.toUTCString()+ ';';
}

//function to write layers changes to cookies
function writeLayerCookie(name, value) {
	var layerswitch = document.getElementById('netkaart-layers');
	if (layerswitch!=null) {
		if (layerswitch.checked) {
			writeCookie('layers-'+name,value);
		}
	}
}

//function to write language cookie
function saveChangedLanguage() {
	var language = document.getElementById("netkaart-language");
	var saveLanguage = language.options[language.selectedIndex].value;
	writeCookie('language-selected',saveLanguage);
	 $.ajax({
	        url: window.location.href,
	        headers: {
	            "Pragma": "no-cache",
	            "Expires": -1,
	            "Cache-Control": "no-cache"
	        }
	    }).done(function () {
	        window.location.reload(true);
	    });
}

//function to save state of layer save button in settings
function saveButtonState (button) {
	var buttonswitch = document.getElementById(button);
	if (buttonswitch!=null) {
		if (buttonswitch.checked) {
			writeCookie('button-'+button,'ON');
			onViewChange();
		} else {
			writeCookie('button-'+button,'OFF');
		}
	}
}

function onViewChange () {
	var element = document.getElementById('netkaart-view');
	if (element.checked) {
//		console.log(map.getZoom()+'/'+map.getCenter().lat.toFixed(4)+'/'+map.getCenter().lng.toFixed(4));
		writeCookie('zoomcenter',map.getZoom()+'/'+map.getCenter().lat.toFixed(4)+'/'+map.getCenter().lng.toFixed(4));
	};
}

//function to save layer changes to cookie
function onLayerChange (e) {
	if (Labellayer) {
		map.removeLayer(Labellayer)
	};
	if (e.type=='baselayerchange') {
//		console.log('Baselayer : '+e.name);
		writeLayerCookie('baselayer',e.name);
		if (e.name=='Esri Light') {
			Labellayer = EsriLightlayerLabels;
			map.addLayer(Labellayer);
		} else if (e.name=='Esri Dark'){
			Labellayer = EsriLightlayerLabels;
			map.addLayer(Labellayer);
		} else {
			Labellayer = false;
		}
	}
	if (e.type=='overlayadd') {
		writeLayerCookie(e.layer.options.orignaam,'ON');
	}
	if (e.type=='overlayremove') {
		writeLayerCookie(e.layer.options.orignaam,'OFF');
	}
}

//function to getcookie value
function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i = 0; i <ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') {
          c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
          return c.substring(name.length,c.length);
      }
  }
  return "";
} 

// Function to clear selection used after cliking on an ampty part of the map
function clearSelection(){
  var selection = ('getSelection' in window)
    ? window.getSelection()
    : ('selection' in document)
      ? document.selection
      : null;
//  console.log (selection);
  if ('removeAllRanges' in selection) {
	  selection.removeAllRanges();
  }
  else if ('empty' in selection) {
	  selection.empty();
  }
};

// Vertaal function
//get selected language from cookie
var language = getCookie("netkaart-language-selected");
var languagelines = "";
//console.log(language);
if (language != "") {
	languagelines = $.ajax({
        type: "GET",
        url: language+'.txt',
        async: false
    }).responseText.split("\n");
	for (var i = 0; i < languagelines.length; i++) {
//		console.log(languagelines[i]);
		if (languagelines[i].substring(0,2) != "/*" && languagelines[i].substring(0,2) != "//") {
			languagelines[i]=languagelines[i].split("=");
		}
	}
}

function vertaal(tekst) {
//	console.log
	if (languagelines != '') {
		var temp = '';
		for (var i = 0; i < languagelines.length; i++) {
			if (languagelines[i][0]==tekst) {
				tekst = languagelines[i][1];
			}
		}
	}
//	console.log(tekst);
	return tekst;
}

function encode_utf8(s) {
  return unescape(encodeURIComponent(s));
}

function decode_utf8(s) {
  return decodeURIComponent(escape(s));
}

// =============================== Load the stijlen.xml file ======================
// the stijlen.xml file
var xmlhttp, xmlDoc;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp=new XMLHttpRequest();
}
else {// code for IE6, IE5
      xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
}
xmlhttp.open("GET", CommonFileDir+"stijlen.xml", false);
xmlhttp.send();
xmlDoc = xmlhttp.responseXML;
// XML is now in xmlDoc variable

// =============================== Settings sidebar definitions ====================
//Define the settings menu
var SettingsHTML = 	'<p><form name="NetkaartSettingsForm">'+
						'<div class="netkaart-form"><fieldset class="netkaart-settings">'+
				        '<div class="netkaart-settings-row"><div class="netkaart-settings-question">'+vertaal('Volg locatie')+'</div>'+
							'<div class="netkaart-settings-switch">'+ 
								'<input id="netkaart-locate" class="netkaart-settings-toggle netkaart-settings-toggle-round-flat" type="checkbox" onclick="changeFollow();">'+
								'<label for="netkaart-locate"></label>'+
							'</div>'+
						'</div>'+
					    '<div class="netkaart-settings-row"><div class="netkaart-settings-question">'+vertaal('Layers opslaan')+'</div>'+
							'<div class="netkaart-settings-switch">'+ 
								'<input id="netkaart-layers" class="netkaart-settings-toggle netkaart-settings-toggle-round-flat" type="checkbox" onclick="saveButtonState(\'netkaart-layers\');">'+
								'<label for="netkaart-layers"></label>'+
							'</div>'+
						'</div>'+
				        '<div class="netkaart-settings-row"><div class="netkaart-settings-question">'+vertaal('View opslaan')+'</div>'+
							'<div class="netkaart-settings-switch">'+ 
								'<input id="netkaart-view" class="netkaart-settings-toggle netkaart-settings-toggle-round-flat" type="checkbox" onclick="saveButtonState(\'netkaart-view\');">'+
								'<label for="netkaart-view"></label>'+
							'</div>'+
						'</div>'+
				        '<div class="netkaart-settings-row"><div class="netkaart-settings-question">'+vertaal('Taal')+'</div></div>'+
				        		'<select id="netkaart-language" onchange="saveChangedLanguage()">'+
				        			'<option value="nl">'+vertaal('Nederlands')+'</option>'+
				        			'<option value="dk">'+vertaal('Deens')+'</option>'+
				        			'<option value="de">'+vertaal('Duits')+'</option>'+
				        			'<option value="uk">'+vertaal('Engels')+'</option>'+
//				        			'<option value="fr">'+vertaal('Frans')+'</option>'+
				        			'<option value="nds">'+vertaal('Nedersaksisch')+'</option>'+
				        		'</select>'+
//				        '<button onclick="saveNetkaartSettings()" class="netkaart-settings-button">Opslaan</button>'+
	             	    '</fieldset></div>'+
	             	  '</form><p>'+
						'<h3>'+vertaal('Over de netkaart')+':</h3><ul>'+
						'<li><a target="_blank" href="https://www.hoogspanningsnet.com/netkaart/handleiding/">'+vertaal('Handleiding')+'</a></li>'+
						'<li><a target="_blank" href="https://www.hoogspanningsnet.com/about/colofon/">'+vertaal('Colofon')+'</a></li>'+
						'<li><a id="legend-feedback" href="PleaseEnableJavascript.html" onclick="FeedbackformDummy();return false;">'+vertaal('Feedback')+'?</a></li>'+
	 					'<li><a target="_blank" href="https://www.hoogspanningsnet.com/netkaart/releasenotes-shortlist/">'+vertaal('Release notes')+'</a></li>'+
						'<li><a target="_blank" href="https://www.hoogspanningsnet.com/netkaart/disclaimer/">'+vertaal('Disclaimer')+'</a></li>'+
						'<li><a target="_blank" href="https://creativecommons.org/licenses/by-nc-nd/4.0/">'+vertaal('Copyrights')+'</a></li></ul></div>';

var SettingsTitleHTML = '<h2>'+vertaal('Instellingen')+'</h2>';

// =============================== Legend sidebar settings =========================
//Function to shown all the colors used in de netkaart as a legend
function kleurCellen() {
	var x = xmlDoc.getElementsByTagName("Spanning");
	var tmpstr = '';
	// reduce the 3 below if we get higher voltages
	for (i = 3; i <x.length; i++) {
		var naam = x[i].getAttribute("Naam");
		if (naam != 'Spanningsloos') {
			var spannaam = naam.substr(naam.indexOf("Netvlak ") + 8)+' kV';
			var lijnstijl = xmlDoc.getElementsByTagName('Lijn');
			var kablstijl = xmlDoc.getElementsByTagName('Kabel');
			tmpstr = tmpstr + '<div class="netkaart-legend-row">'+
							  		'<div class="netkaart-legend-cell-color" style="color:#'+ lijnstijl[i].getAttribute("Color") +'; opacity:'+ lijnstijl[i].getAttribute("Alpha")/100 +';">&#9608;&#9608;&#9608;&#9608;&#9608;&#9608;&#9608;&#9608;</div>'+
									'<div class="netkaart-legend-cell-color" style="color:#'+ kablstijl[i].getAttribute("Color") +'; opacity:'+ kablstijl[i].getAttribute("Alpha")/100 +';">&#9608;&#9608;&#9608;&#9608;&#9608;&#9608;&#9608;&#9608;</div>'+
									'<div class="netkaart-legend-cell-text">'+ spannaam +'</div>'+
							 '</div>';
		} else {
			tmpstr = tmpstr + '<div class="netkaart-legend-row">'+
	  								'<div class="netkaart-legend-cell-color" style="top:2px;"><img src="files/legenda-spanningsloos.png"></div>'+
	  								'<div class="netkaart-legend-cell-color"><img src="files/legenda-spanningsloos.png"></div>'+
	  								'<div class="netkaart-legend-cell-text">'+vertaal('Spanningsloos')+'</div>'+
	  							'</div>';
		}
	}
	return tmpstr;
}	

//HTML to show inside the legendmenu 
var LegendTitleHTML = '<div class="netkaart-legend"><h2>'+vertaal('Legenda')+'</h2>'+
				'<div class="netkaart-legend-table">'+
					'<div class="netkaart-legend-row">'+
						'<div class="netkaart-legend-cell-head">'+vertaal('Lijn')+'</div>'+
						'<div class="netkaart-legend-cell-head">'+vertaal('Kabel')+'</div>'+
					'</div>'+ kleurCellen() +
				'</div>';


//Define attributions for the different basemaps
var HSNAttr = '&copy; <a href="https://www.hoogspanningsnet.com">Hoogspanningsnet.com</a>';
var LeafletAttr = '<a href="http://leafletjs.com">Leaflet</a>';
var OsmAttr = 'Map data &copy; <a href="http://openstreetmap.org" target="_blank">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC-BY-SA</a>';
var EsriAttr = 'HERE, DeLorme, MapmyIndia, OpenStreetMap contributors'; 

// The token for the Esri Light en Dark baselayers
var EsriToken = 'RPK64bYh3PR9MxihVbDJD4ZrtGjp50PARPUPGNdvoFRSkfjMRJ8STwiGe6LJ75ST';