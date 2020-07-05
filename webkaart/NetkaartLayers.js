/*
 * Copyright (c) 2016 Dominique Cavailhez
 * https://github.com/Dominique92
 * Supported both on Leaflet V0.7 & V1.0
 *
 * geoJSON layers to access www.refuges.info geographic flows
 */

//=========================== Define basemaps ==============================
//Define Tile layers - Esri Light
var EsriLightlayer = L.esri.basemapLayer('Gray',{
	naam: 'Esri Light',
	maxZoom: 16,
	token: EsriToken,
	attribution: HSNAttr + ' | ' + EsriAttr,
});

//set initial labellayer to false;
var EsriLightlayerLabels = L.esri.basemapLayer('GrayLabels',{});

//Define Tile layers - Esri Dark
var EsriDarklayer = L.esri.basemapLayer('DarkGray',{
	naam: 'Esri Dark',
	maxZoom: 16,
	token: EsriToken,
	attribution: HSNAttr + ' | ' + EsriAttr,
});

//Define Labels for Tile layers - Esri Dark
var EsriDarklayerLabels = L.esri.basemapLayer('DarkGrayLabels',{});

//Define Tile layers - Open Streetmap
var osmStreetlayer = L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
	naam: 'Open Streetmap',
	maxZoom: 18,
	subdomains: ['a','b','c'],
	attribution: HSNAttr + ' | ' + OsmAttr,
});

//Define Google Streets layer
var googleStreetslayer = L.gridLayer.googleMutant({
	naam: 'Google Streets',
	type: 'roadmap',
	attribution: HSNAttr,
});

//Define Google Hybrid layer
var googleHybridlayer = L.gridLayer.googleMutant({
	naam: 'Google Hybrid',
	type: 'hybrid',
	attribution: HSNAttr,
});

//Define Google Satellite layer
var googleSatlayer = L.gridLayer.googleMutant({
	naam: 'Google Satellite',
	type: 'satellite', 
	attribution: HSNAttr,
});

//=========================== Define Overlays ==============================
//Function to style the Stationsicon labels
function styleIconlabels(feat) {
	return {"iconUrl": 'iconnew.php?spans=' + feat.Spanningen + '&naam=' + feat.Naam, 
			"iconAnchor": [0,0], 
			"className": 'netkaart-stationsicon',
	}
}

// Define Stationsiconen layer
L.GeoJSON.Ajax.Stationsiconen = L.GeoJSON.Ajax.extend({
	options: {
		urlGeoJSON: 'layerdata.php',
		argsGeoJSON: {
			type: 'stat',
		},
		bbox: true,
		zoom: true,
		orignaam: 'stationsiconen',
		naam: vertaal('Stationsiconen'),
		pane: 'netkaart-stationicon-pane',
	  	style: function(feature) {
			return styleIconlabels(feature.properties);
 		},
	}
});

//Function to style Knooppunten Labels
function styleKnpplabels(feat) {
	return {"iconUrl": 'iconnew.php?type=knpp&spans=' + feat.Spanning + '&naam=' + decode_utf8(feat.Naam), 
			"iconAnchor": [15,-3], 
			"className": 'netkaart-knooppunticon',
	}
}

// Define Knooppuntenlayer
L.GeoJSON.Ajax.Knooppunten = L.GeoJSON.Ajax.extend({
	options: {
		urlGeoJSON: 'layerdata.php',
		argsGeoJSON: {
			type: 'knpp',
		},
		bbox: true,
		zoom: true,
		orignaam: 'knooppunten',
		naam: vertaal('Knooppunten'),
		pane: 'netkaart-knoopicon-pane',
	  	style: function(feature) {
			return styleKnpplabels(feature.properties);
 		},
	}
});

//Define Mastenlayer
var mastIcoonRenderer = L.canvas({padding: 0.03, pane: 'markerPane'});
L.GeoJSON.Ajax.MastIconen = L.GeoJSON.Ajax.extend({
	options: {
		urlGeoJSON: 'layerdata.php',
		argsGeoJSON: {
			type: 'mast',
		},
		bbox: true,
		zoom: true,
		orignaam: 'masten',
		naam: vertaal('Masten'),
		pane: 'netkaart-masticon-pane',
		pointToLayer: (feature, latlng) => {
			return new L.MastIconMarker(latlng, {
				renderer: mastIcoonRenderer,
				voltage: feature.properties.Spanning,
				name: decode_utf8(feature.properties.Naam.split("-").pop())
			});
		},
	}
});

var verbindingenRenderer = L.canvas({padding: 0});
// Style functie voor de verbindingen layer
function styleVerbinding(feat) {
	var x = xmlDoc.getElementsByTagName("Spanning");
		for (i = 0; i <x.length; i++) { 
			if (feat.Spanning>= parseFloat(x[i].getAttribute("LaagGrens")) && feat.Spanning <= parseFloat(x[i].getAttribute("HoogGrens"))) {
				var stijl = xmlDoc.getElementsByTagName(feat.HoofdType);
//				console.log(feat.Spanning);
				return {"color": "#" + stijl[i].getAttribute("Color"), "weight": Math.ceil(stijl[i].getAttribute("Width")), "opacity": stijl[i].getAttribute("Alpha")/100, "renderer": verbindingenRenderer};
			}
	  }	
 }

// Define Verbindingen layer
L.GeoJSON.Ajax.Verbindingen = L.GeoJSON.Ajax.extend({
	options: {
		urlGeoJSON: 'layerdata.php',
		argsGeoJSON: {
			type: 'verb'
		},
		bbox: true,
		zoom: true,
		orignaam: 'verbindingen',
		naam: vertaal('Verbindingen'),
	  	style: function(feature) {
			return styleVerbinding(feature.properties);
 		},
	}
});

//Style functie voor de stationsterreinen layer
function styleTerreinen(feat) {
	var x = xmlDoc.getElementsByTagName("Spanning");
	for (i = 0; i <x.length; i++) { 
		if (feat.Spanning>= parseFloat(x[i].getAttribute("LaagGrens")) && feat.Spanning <= parseFloat(x[i].getAttribute("HoogGrens"))) {
			var kleuren = {};
			var stijl = xmlDoc.getElementsByTagName('Rand');
			kleuren.color = "#" + stijl[i].getAttribute("Color");
			kleuren.weight = Math.ceil(stijl[i].getAttribute("Width"));
			kleuren.opacity =  stijl[i].getAttribute("Alpha")/100;
			var stijl = xmlDoc.getElementsByTagName('Vulling');
			kleuren.fillColor = "#" + stijl[i].getAttribute("Color");
			kleuren.fillOpacity = stijl[i].getAttribute("Alpha")/100;
			return kleuren;
		}
	 }	
  }


// Define Stationsterreinen layer
L.GeoJSON.Ajax.StationsTerrein = L.GeoJSON.Ajax.extend({
	options: {
		urlGeoJSON: 'layerdata.php',
		argsGeoJSON: {
			type: 'terr',
		},
		bbox: true,
		zoom: true,
		orignaam: 'stationsterreinen',
		naam: vertaal('Stationsterreinen'),
	  	style: function(feature) {
			return styleTerreinen(feature.properties);
 		},
	}
});

