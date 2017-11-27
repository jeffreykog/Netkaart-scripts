//Define the map
if (getCookie("netkaart-button-netkaart-view")=='ON' && getCookie("netkaart-zoomcenter")!='') {
	zoomcenter = getCookie("netkaart-zoomcenter").split('/');
	var mapcenter = L.latLng(zoomcenter[1], zoomcenter[2]);
	var mapzoom = zoomcenter[0];
} else {
	var mapcenter = [52, 5];
	var mapzoom = 6;
}
;
map = new L.Map('map', {
  	center: mapcenter, 
  	zoom: mapzoom,
    condensedAttributionControl: false, // don't include default, as we are setting options below
});

// Add a spinner to the mao to user can see its busy doing something
var loadingControl = L.Control.loading({
    separate: true,
    position: 'bottomleft',
});
map.addControl(loadingControl);

// Add hash to the URL, this contains the curront zoom level and coordinates, makes bookmarking easy========
var hash = new L.Hash(map);


function toDate(dateStr) {
    var parts = dateStr.split("-");
    return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
}

//Show Alert or Welcome window if last-visited cookie is not set (e.g. user is new or hasnt been seen more than 30 days)
now = new Date().getTime();
if (AlertMessage != '') { 						// show ALert window
	var AlertWindow = L.control.window(map,	{
		title: AlertTitle,
		maxWidth:400,
		content: AlertMessage,
		modal: true,
	}).show();
} else if (getCookie('netkaart-last-visited')=='') {
	var AlertWindow = L.control.window(map,	{
		title: WelcomeTitle,
		maxWidth:400,
		content: WelcomeMessage,
		modal: true,
	}).show();
} else if (	(toDate(NewInfoDate) <= now && 
			now < (toDate(NewInfoDate)+(NewInfoShowDays*24*3600*1000))) && 
			Number(getCookie('netkaart-info-seen')) < toDate(NewInfoDate)) {
	writeCookie('info-seen',now);
	var AlertWindow = L.control.window(map,	{
		title: NewInfoTitle,
		maxWidth:400,
		content: NewInfoMessage,
		modal: true,
	}).show();
}

//write visited cookie
writeCookie('last-visited',new Date().getTime());

//Attrribution set custom emblem and prefix
L.control.condensedAttribution({
	emblem: '<div class="customattrib-emblem-wrap"><img src="files/favicon-1.png"/></div>',
	prefix: HSNAttr + ' | ' + LeafletAttr,
}).addTo(map);

//Add Locate control to the map
var locateControl = new L.control.locate({
    position: 'topleft',
    setView: changeFollowSwitch(),
    locateOptions: {maxZoom: 15},
    strings: {
        title: vertaal('Naar mijn huidige locatie!'),
        metersUnit: "meter",
        popup: vertaal('U bent binnen')+' {distance} {unit} '+vertaal('van dit punt'),        
    }
}).addTo(map);

//Add the balloon sidebar to the map, this contains the item information when clicked
var balloonbar = L.control.sidebar('balloonbar', {
  position: 'right',
  autoPan: false,
  closeButton: true,
});
map.addControl(balloonbar);

//function to track the changing of the follow postions switch in the settings sidebar
function changeFollow () {
	if (locateControl.options.setView !='none') {
		var buttonswitch = document.getElementById('netkaart-locate');
		if (buttonswitch!=null) {
			if (buttonswitch.checked) {
				return locateControl.options.setView = 'always';
			} else {
				return locateControl.options.setView = 'once';
			}
		}
	} else {
		return 'none';
	}
}

//function to track the changing of the follow postions switch in the settings sidebar
//TODO do we need this?
function changeFollowSwitch () {
	var buttonswitch = document.getElementById('netkaart-locate');
	if (buttonswitch!=null) {
		if (buttonswitch.checked) {
			return 'always';
		} else {
			return 'once';
		}
	} else {
		return 'once';
	}
}

//Add search control to the map
$("#searchContainer").GeoJsonAutocomplete({
	geojsonServiceAddress: 'search.php',
	placeholderMessage: vertaal('Zoek...'),
	searchButtonTitle: vertaal('Zoeken!'),
	clearButtonTitle: vertaal('Wissen'),
	notFoundMessage: vertaal('Geen zoekresultaat'),
	notFoundHint: vertaal('Pas uw criteria aan'),
	limit: 10,
	pointGeometryZoomLevel: 13
});

//Add settingsdialog to map
var legendbar = L.control.sidebar('legendbar', {
  position: 'left',
  autoPan: false,
  closeButton: false,
});
map.addControl(legendbar);
legendbar.setContent(LegendTitleHTML);

//Add toggle button to show side bar to map
var Legendtoggle = L.easyButton({
	  states: [{
	    stateName: 'show-legendbar',
	    icon: 'fa-bars',
	    title: vertaal('Legenda weergeven'),
	    onClick: function(control) {
	      Sidebartoggle.state('show-sidebar');
	      sidebar.hide();	
	      control.state('hide-legendbar');
	      legendbar.show();
	    }
	  }, {
		stateName: 'hide-legendbar',
	    icon: 'fa-bars',
	    title: vertaal('Legenda verbergen'),
	    onClick: function(control) {
	      control.state('show-legendbar');
	      legendbar.hide();	    },
	  }]
	});
Legendtoggle.addTo(map);

//HTMLto show inside the slidemenu 
//var SettingsHTML = $.ajax({type: "GET", url: "sidebar.html", async: false}).responseText;
//Add settingsdialog to map
var sidebar = L.control.sidebar('sidebar', {
    position: 'left',
    autoPan: false,
    closeButton: false,
});
map.addControl(sidebar);
sidebar.setContent(SettingsTitleHTML);

//Add toggle button to show side bar to map
var Sidebartoggle = L.easyButton({
	  states: [{
	    stateName: 'show-sidebar',
	    icon: 'fa-cog',
	    title: vertaal('Instellingen weergeven'),
	    onClick: function(control) {
	      Legendtoggle.state('show-legendbar');
	      legendbar.hide();
	      control.state('hide-sidebar');
	      sidebar.show();
	    }
	  }, {
		stateName: 'hide-sidebar',
	    icon: 'fa-cog',
	    title: vertaal('Instellingen verbergen'),
	    onClick: function(control) {
	      control.state('show-sidebar');
	      sidebar.hide();	    
	    },
	  }]
	});
Sidebartoggle.addTo(map);

if (getCookie('netkaart-button-netkaart-layers')=='ON') {
	if (getCookie('netkaart-layers-baselayer')=='Esri Dark') {
		EsriDarklayer.addTo(map);
		var Labellayer = EsriDarklayerLabels;
		Labellayer.addTo(map);
	} else if (getCookie('netkaart-layers-baselayer')=='Open StreetMap') {
		osmStreetlayer.addTo(map);
	} else if (getCookie('netkaart-layers-baselayer')=='Google Streets') {
		googleStreetslayer.addTo(map);
	} else if (getCookie('netkaart-layers-baselayer')=='Google Hybrid') {
		googleHybridlayer.addTo(map);
	} else if (getCookie('netkaart-layers-baselayer')=='Google Satellite') {
		googleSatlayer.addTo(map);
	} else {
		EsriLightlayer.addTo(map);
		var EsriLightlayerLabels = L.esri.basemapLayer('GrayLabels',{}).addTo(map);
		var Labellayer = EsriLightlayerLabels;
		Labellayer.addTo(map);
		//Write the layer value to a cookie
		writeLayerCookie('baselayer',EsriLightlayer.options.naam);
	}
} else {
	EsriLightlayer.addTo(map);
	var EsriLightlayerLabels = L.esri.basemapLayer('GrayLabels',{}).addTo(map);
	var Labellayer = EsriLightlayerLabels;
	Labellayer.addTo(map);
	//Write the layer value to a cookie
	writeLayerCookie('baselayer',EsriLightlayer.options.naam);
}

//Add Tile layers to a baselayer variable
var baseLayers = {
	"Esri Light": EsriLightlayer,
	"Esri Dark": EsriDarklayer,
	"Open StreetMap": osmStreetlayer,
	"Google Satellite": googleSatlayer,
	"Google Streets": googleStreetslayer,
	"Google Hybrid": googleHybridlayer,
};

function FeedbackformDummy() {
	return false;
};

function validateEmail(email) {
	  var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	  return re.test(email);
}

function ShowFeedbackForm(id, fout) {
	$.get('feedbackform.php?ID='+id, function( data ) {
		balloonbar.setContent('<div class="feedbackwrapper">'+fout+'<p>'+data+'</div');		
		$('#balloon-feedbacksent').click(function(){ 
			CheckFeedbackValues(id);	
		});
	});
}

function ShowEdititemForm(id) {
	$.post('edititem3.php?ID='+id, function( data ) {
//	$.post('edititem.php?ID='+id, function( data ) {
		balloonbar.setContent(data);		
		$('#balloon-edititemsend').click(function(){ 
			CheckEdititemValues (id);
		});
		$("#balloon-login").click(function( data ){ 
			ShowLoginForm (id,'');
		});
	});
}

function ShowLoginForm(id, tekst) {
	$.post('loginedit.php?ID='+id, function( data ) {
		balloonbar.setContent(tekst+data);		
		$('#edititem-login').click(function(){ 
			CheckLoginValues (id);
		});
	});
}
function CheckLoginValues(id) {
	var queryString = $('#webkaart-loginform').serialize();
	$.post('login.php?ID='+id, queryString, function( data ) {
		if (data==='logged in') {
			ShowEdititemForm(id);
		} else {
			ShowLoginForm(id, data);
		}
	});
	return false;
};

function CheckEdititemValues(id) {
	var queryString = $('#editdata').serialize();
//	$.post('edititem.php?ID='+id+'&submit=Invoeren!', queryString, function( data ) {
	$.post('edititem3.php?ID='+id+'&submit=Invoeren!', queryString, function( data ) {
		balloonbar.setContent(data);
		$('#balloon-edititemsend').click(function(){ 
			CheckEdititemValues (id);
		});
	});
	return false;
};

function SaveToGithub(id, naam, mail, onde, fbtx, geta){
	$.get('feedback.php?ID='+id+'&gt='+geta+'&nm='+naam+'&ml='+mail+'&on='+onde+'&tx='+fbtx, function( data ) {
		balloonbar.setContent(data);
	});
	return false;
}

function CheckFeedbackValues(id) {
	var naam = document.forms["feedback"]["feedbackname"].value;
	var mail = document.forms["feedback"]["feedbackemail"].value;
	var onde = document.forms["feedback"]["feedbacksubject"].value;
	var fbtx = encodeURIComponent(document.forms["feedback"]["feedbackmessage"].value);
	var furl = document.forms["feedback"]["url"].value;
	var geta = document.forms["feedback"]["feedbackgetal"].value;
	if 	(	naam.length>2 && 
			onde.length>10 &&
			fbtx.length>10 && 
			validateEmail(mail) &&
			furl.length==0 &&
			geta.length==4
		) 
	{
		fbtx = fbtx.replace(/\r?\n/g, '<br />');
		balloonbar.setContent(SaveToGithub(id, naam, mail, onde, fbtx, geta));	
	} else {
		ShowFeedbackForm(id,vertaal('FOUT: geen valide input')+'<p>');
	}
	return false;
};

//Define function to show the feature balloon
function FeatureBalloon(feature, layer) {
	layer.on('click', function(e){
		$.get("balloon4.php?ID="+feature.properties.ID, function( data ) {
			balloonbar.setContent(data);
			balloonbar.show();

			$("#balloon-feedback").click(function(){ 
				ShowFeedbackForm(feature.properties.ID,'');
			});
			$("#balloon-edititem").click(function(){ 
				ShowEdititemForm(feature.properties.ID,'');
			});
		});
	});
	return false;
};

//Define function to hide the ballonbar when user click on empty part of map.
map.on('click', function (e) {
	balloonbar.setContent('');
	balloonbar.hide();
});

//Define function to unselect text automagically after move.
map.on('moveend', function (e) {
	setTimeout(function(){ 
		clearSelection();
	}, 0);
});

//Define function to show feature tooltip and balloon
function FeatureTooltip(feature, layer) {
	FeatureBalloon(feature, layer);
	//add listener for on mouseover and mouseout
	var curIconUrl;
	layer.on('mouseover', function (e) {
		curIconUrl = layer.options.icon.options.iconUrl;
		layer.options.icon.options.iconUrl = layer.options.icon.options.iconUrl+'&scale=true';
		layer.setIcon(layer.options.icon);
	}); 
	layer.on('mouseout', function (e) {
		layer.options.icon.options.iconUrl = curIconUrl;
		layer.setIcon(layer.options.icon);
	}); 
};

//Add Stationterreinen layer to map
var TerrGeoJSONlayer = new L.GeoJSON.Ajax.StationsTerrein({
	onEachFeature: FeatureBalloon, 
});
if (getCookie('netkaart-layers-stationsterreinen')!=='OFF') {
	TerrGeoJSONlayer.addTo(map);
};

//Add Verbindingen layer to map
var VerbGeoJSONlayer = new L.GeoJSON.Ajax.Verbindingen({
	onEachFeature: FeatureBalloon, 
});
if (getCookie('netkaart-layers-verbindingen')!=='OFF') {
	VerbGeoJSONlayer.addTo(map);
};

//Add Masten layer to map
var MastGeoJSONlayer = new L.GeoJSON.Ajax.MastIconen({
	onEachFeature: 	FeatureTooltip, 
});
if (getCookie('netkaart-layers-masten')!=='OFF') {
	MastGeoJSONlayer.addTo(map);
};

//Add Knooppunten layer to map
var KnppGeoJSONlayer = new L.GeoJSON.Ajax.Knooppunten({
	onEachFeature: FeatureTooltip, 
});
if (getCookie('netkaart-layers-knooppunten')!=='OFF') {
	KnppGeoJSONlayer.addTo(map);
};

//Add StationsIconen layer to map
var StatGeoJSONlayer = new L.GeoJSON.Ajax.Stationsiconen({
	onEachFeature: FeatureTooltip, 
});
if (getCookie('netkaart-layers-stationsiconen')!=='OFF') {
	StatGeoJSONlayer.addTo(map);
};

//Add Netkaart layers to geolayers variable
var geoLayers = {
};
geoLayers[TerrGeoJSONlayer.options.naam] = TerrGeoJSONlayer;
geoLayers[VerbGeoJSONlayer.options.naam] = VerbGeoJSONlayer;
geoLayers[MastGeoJSONlayer.options.naam] = MastGeoJSONlayer;
geoLayers[KnppGeoJSONlayer.options.naam] = KnppGeoJSONlayer;
geoLayers[StatGeoJSONlayer.options.naam] = StatGeoJSONlayer;

function saveNetkaartSettings() {
	var myform = document.forms[0].elements["leaflet-base-layers"];
	for (i = 0; i < myform.length; i++) {
	};
	var myformx = document.querySelectorAll("checkbox.leaflet-control-layers-selector");
}

//Add layer switch control to map using the baselayers and geolayers variables
var layercontrol = new L.control.layers(baseLayers,geoLayers,{collapsed:true}).addTo(map);

//Copy the layer control to the sidebar menu
$('#sidebar').append(layercontrol.onAdd(map));
//Hide the original layer control icon
$('.leaflet-top.leaflet-right').hide();
$('#sidebar').append(SettingsHTML);
$("#legend-feedback").click(function(){ 
	ShowFeedbackForm('','');
	balloonbar.show();
});

//add eventlisteners for layer change tracking
map.on('baselayerchange',onLayerChange);
map.on('overlayadd',onLayerChange);
map.on('overlayremove',onLayerChange);
map.on('zoomend',onViewChange);
map.on('moveend',onViewChange);

//get selected language from cookie
var language = getCookie("netkaart-language-selected");
if (language != "") {
	var element = document.getElementById('netkaart-language');
	element.value = language;
}

//get save layer checkboxstate from cookie
var saveView = getCookie("netkaart-button-netkaart-layers");
if (saveView != "") {
	var element = document.getElementById('netkaart-layers');
	if (saveView=='ON') {
			element.checked = true;
		} else {
			element.checked = false;
		}
}

//get save view checkboxstate from cookie
var saveView = getCookie("netkaart-button-netkaart-view");
if (saveView != "") {
	var element = document.getElementById('netkaart-view');
	if (saveView=='ON') {
			element.checked = true;
		} else {
			element.checked = false;
		}
}

// Load the nice select for the settingsmenu
$(document).ready(function() {
	$('select').niceSelect();
});


