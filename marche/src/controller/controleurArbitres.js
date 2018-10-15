 app.controller('controleurArbitres',function($scope, $rootScope, $stateParams,$location, arbitreProvider, arenaProvider,$http){
 	$rootScope.sync = function(){
 		console.log($rootScope.arbitres);
// 			$rootScope.arbitres =arbitreProvider.getArbitres();//Annonces.get({method:"GET"});// arbitreProvider.getArbitres();
 	};
 	//console.log("arbitres est:" +$scope.arbitres);
 	var httpLoad= true;
 	
 	
 //	$scope.requis =[];
 //	$scope.arenas =[];
 //	$scope.users =[];
 	
 		if($scope.arbitres==undefined){
 			$rootScope.arbitres = [];
 			$rootScope.arenas = [];
 			$rootScope.users = [];
 			$rootScope.requis= [];
 			$rootScope.arbitres =arbitreProvider.getArbitres($rootScope.arbitres,$rootScope.users);
 		}
 			console.log("longueur arbitre: "+$rootScope.arbitres.length);
 			if($rootScope.arbitres.length==0){
 				$rootScope.arbitres =arbitreProvider.getArbitres($rootScope.arbitres,$rootScope.users);
 				$rootScope.arenas=arenaProvider.getArenas($rootScope.arenas); 
 				$rootScope.requis=arbitreProvider.getRequis(); 
 				
 			}
 		
 	
 	
 	
 		
 
    
    	$scope.contexte2 = ["offre", "cherche"];
    	$rootScope.ajouterOffre = function(a){
    		a.contexte="arbitre";
    		$rootScope.arbitres=arbitreProvider.creer(a,$rootScope.arbitres.length);
    		
    		};
    		
    	console.log("param passé dans l'URL: "+$stateParams.id);
    	$scope.b = $stateParams.id;
    	$scope.rId = $stateParams.rId;
    	
    	$rootScope.confirmerSuppression =function(b){
    			angular.forEach($rootScope.arbitres,function(value,key){
    			if(value.id==$stateParams.id){
    				$rootScope.arbitres.splice(key,1);
    				console.log("Trouvé! 2");
    			}
    			$location.path( "/arbitres" );
    			
    		});

    		};
    	$rootScope.infirmerSuppression =function(a){
    		console.log("arbitres est dans infirmerSuppression:" +$rootScope.arbitres);
    			$location.path( "/arbitres" );
    		};
    		
    	$rootScope.detailsArbitre = function(a){
    		console.log("param passé dans l'URL 3: "+$stateParams.id);
    		angular.forEach($rootScope.arbitres,function(value,key){
    			if(value.id==$stateParams.id){
    				$scope.arbitre = value;
    				//console.log("Trouvé!  " + JSON.strignify($scope.arbitre));
    			}
    			
    		});
    		};
    		
    		$rootScope.supprimerArbitre = function(a){
    		console.log("param passé dans l'URL 4: "+$scope.b);
    		angular.forEach($rootScope.arbitres,function(value,key){
    			if(value.id==$scope.b ){
    				$rootScope.arbitre = value;
    				console.log("Trouvé!");
    			}
    			
    		});
    		};
    		
    		
    		$rootScope.postuler=function(){
    			
    			$http.post("/scriptsphp/sendPostulation.php",
    			{
    				expediteur : "42",
    				recepteur : [$scope.arbitre.userId],
    				corps : "L'offre no "+$scope.arbitre.id+" a un postulant",
    				titre : "Yeau"
    			});
    			
    		};
    	
    		//$scope.getTitreRequis
    		/*function(r){
    			angular.forEach($scope.requis,function(valeur, clef){
    				console.log(JSON.stringify(valeur)+" ///  "+clef+" //// "+$stateParams.rId);
    				if(valeur.id==$stateParams.rId){
    					return valeur.enonce_fr;
    				}
    				
    			});                                      
    		};*/
    		
    		
    });