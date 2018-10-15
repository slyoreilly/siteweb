
var positions = [{"id":"A","intitule":"Attaquant"},{"id":"G","intitule":"Gardien"},{"id":"D","intitule":"Défenseur"},{"id":"B","intitule":"Arbitre"}];

app.service('positionProvider', function(){
	
		this.getPositions = function(){return positions;};
		this.creer =function(j){positions.push(j); return positions;};
	
	});
	

