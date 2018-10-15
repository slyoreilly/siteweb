"use strict";

var cache = false;
app.config(function($stateProvider){
	var accueilState={
		cache:cache,
		name: 'accueil',
		url:'accueil',
		templateUrl:'js/views/accueil/accueil.html',
		controller: 'accueilControleur'
	};
	var videoState={
		cache:cache,
		name: 'video',
		url:'/video?id',
		templateUrl:'js/views/video/video.html',
		controller: 'videoControleur'
	};
	$stateProvider.state(accueilState);
	$stateProvider.state(videoState);
	
}) ;
