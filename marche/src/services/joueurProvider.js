

var equipes =[{"nomEquipe":"Blancs","equipeId":"0","joueurs":[{"nomJoueur":"Mik","joueurId":"5","position":"G"},{"nomJoueur":"Sylvain","joueurId":"1","position":"A"},{"nomJoueur":"Fred","joueurId":"2","position":"A"}]},
    	{"nomEquipe":"Noirs","equipeId":"1","joueurs":[{"nomJoueur":"Nelly","joueurId":"6","position":"G"},{"nomJoueur":"Marco","joueurId":"3","position":"A"},{"nomJoueur":"Pat","joueurId":"4","position":"A"}]}];

app.service('joueurProvider', function(){
	
		this.getEquipes = function(){return equipes;};
		this.creer =function(j){equipes[0].joueurs.push(j); return equipes;};
	
	});
	
