app.controller('controleurJoueurs',function($scope,joueurProvider,positionProvider){
    	$scope.equipes = joueurProvider.getEquipes();
    	$scope.positions = positionProvider.getPositions();
    	
	$scope.ajouterJoueur = function(j){joueurProvider.creer(j);};
	
	$scope.supprimerJoueur = function(j){
		//$scope.equipes[0].joueurs.push(j);
		
	};
  });
 