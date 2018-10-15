var app = angular.module('appSS');

app.directive('listerJoueurs',function(joueurProvider){
	var equipes=joueurProvider.getEquipes;
	return{restrict:'E',
	template: '<ul ng-repeat="x in equipes"><li>{{x.nomEquipe}}<ul ng-repeat="y in x.joueurs"> <li>{{y.nomJoueur}}</li> </ul>   </li></ul>'
};
});
app.directive('listerArbitres',function(arbitreProvider){
	return{restrict:'E',
	templateUrl: '/marche/views/arbitres/fichearbitre.html'
};
}).directive('lightboxDirective', function() {
  return {
    restrict: 'E', // applied on 'element'
    transclude: true, // re-use the inner HTML of the directive
    templateUrl: '/marche/views/maitre/connexion.html',
    controller:'controleurUser'
  };
});





//| filtrearbitres