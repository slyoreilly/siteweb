"use strict";


app.controller("videoControleur",function($scope,$rootScope,$stateParams,matchsProvider){
$rootScope.titreVid="Videos!";	

$rootScope.lienVid="www.syncstats.com";
if($stateParams.id=='tous'){
	$rootScope.titre="Page de vids!";	
	$scope.contenu="Toutesss mes vidze";
}
$scope.matchs=matchsProvider.getMatchs();

}) ;
