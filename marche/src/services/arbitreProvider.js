

//var arbitres=[{nom:"Gino",userId:"7"}];

app.service('arbitreProvider', function(Annonces, Requis, $location, $rootScope,userProvider) {
	//	var arbitresPro =$resource('/scriptsphp/monreste.php/job/');
	//	arbitresPro.query({},function(arbitres){});

					

	this.getArbitres = function(mesArbitres,mUsers) {

		/*var arbitres = */
		Annonces.query({}, function(job) {
			//    console.log("Une dzobe: "+JSON.stringify(job));
			job.forEach(function(objet, indice) {

				pos = mesArbitres.map(function(e) {
					return e.id;
				}).indexOf(objet.id);
				if (pos < 0) {
					objet.requis = JSON.parse(objet.requis);
					
					var promise= userProvider.getUser(mUsers,objet.userId).then(function(data){
						console.log(" Los Todos "+data);
						 objet.nom =data.nom;
						console.log(" Las nombres "+data.nom);
						return data.nom;}
						).catch(function () {
							 objet.nom ="Utilisateur inconnu";
     						console.log("Promise Rejected");
							return "Utilisateur inconnu";
						});
					
					// objet.nom =promise;
					mesArbitres.push(objet);
					

				}

			});
		});
		console.log("Zarbites2:" + JSON.stringify(mesArbitres));

		return mesArbitres;
	};




	this.getRequis = function() {
		var requis = Requis.query({}, function(mRequis) {

			requis = mRequis;
//			console.log("Requis" + requis + " ");
		});
		return requis;
	};

	this.creer = function(j, ind) {
		console.log("creer executé");
		var nouvArbitre = new Annonces();
		nouvArbitre.montantOffert = j.montantOffert;
		nouvArbitre.userId = j.userId;
		nouvArbitre.contexte2 = j.contexte2;
		nouvArbitre.contexte = "arbitre";
		nouvArbitre.typeJob = 1;
		nouvArbitre.datePublie = "2018-03-22 00:00:00";
		nouvArbitre.dateExpire = "2018-03-29 00:00:00";
		nouvArbitre.statut = 2;
		//nouvArbitre.idLoc=ind+1;
		arbitres.push(nouvArbitre);
		nouvArbitre.$save();
		$location.path("/arbitres");
		//this.getArbitres();
		return arbitres;
	};

}).service('arenaProvider', function($http, $rootScope) {
	this.getArenas = function(mArenas) {

		/*var arenas = */$http({
			method : 'GET',
			url : '/stats2/getArena.php'
		}).then(function(success) {
			for(var a=0;a<success.data.length;a++){
			mArenas.push(success.data[a]);
				
			}
	//		console.log("les vrais arenas " + arenas);
		}, function(error) {
			alert(error);
		});
		return mArenas;
	};

});

