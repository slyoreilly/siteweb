 app.controller('controleurPositions',function($scope,positionProvider){
    	$scope.positions = positionProvider.getPositions();
    	
    	$scope.ajouterPosition = function(p){positionProvider.creer(p);};
    });