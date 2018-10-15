app.controller('controleurUser',function($scope, $rootScope, $http){
	$rootScope.user = {};
	$rootScope.user.username="";
	$rootScope.connexion=function(){
    			
    			$http.post("/scriptsphp/confirme_connexion.php",
    			{
    				courriel : $rootScope.user.username,
    				mdp : $rootScope.user.mdp
    			}).then(function(success) {
			console.log(JSON.stringify(success.data));	
			$rootScope.showLightBox=false;
			if(success.data.statut){
				$rootScope.connecte = true;
			}else{
				$rootScope.connecte = false;
			}
			}
	//		console.log("les vrais arenas " + arenas);
		, function(error) {
			console.log(JSON.stringify(error)+"");	
			alert(error);
		});
    			
    		};
    		if($rootScope.user.username!=null){
    			$rootScope.connecte = true;
    		}
    		else{$rootScope.connecte =false;}
	
	
});