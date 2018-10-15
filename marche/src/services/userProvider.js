//var arbitres=[{nom:"Gino",userId:"7"}];

app.service('userProvider', function($http,$q) {
	//	var arbitresPro =$resource('/scriptsphp/monreste.php/job/');
	//	arbitresPro.query({},function(arbitres){});

	this.getUser = function(mUsers, userId) {
		
						pos = mUsers.map(function(e) {
					return e.id;
				}).indexOf(userId);
		
		


		if(pos<0){
		unUser= $http({
			method : 'GET',
			params:{id:parseInt(userId)},
			url : '/stats2/perso2JSONGET.php'
		}).then(function(success) {
			mUsers.push(success.data);
			return success.data;
			//unUser = 	success.data;
			//			console.log("les vrais users "+userId+" " + JSON.stringify(unUser));
		}, function(error) {
			alert(error);
		}).catch(function () {
     console.log("Promesse rejetée");
							return "Utilisateur inconnu";
});
		
		return unUser;
	
	} else{
		//var unUser=  $q.defer();
		return mUsers[pos];
		//return unUser;
		
		}
};
});


