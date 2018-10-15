app.filter('gardiens',function(){
	return function(joueur){
		if(joueur.position=='G'){return joueur;}
		else{return false;}
	};
}).filter('filtreannonces',function(){
	return function(annonce,contexte){
		var listeAnnonces=[];
		for (var a=0;a<annonce.length;a++){
				if(annonce[a].contexte==contexte){listeAnnonces.push(annonce[a]);}
				
			}
		return listeAnnonces;		
	};
	
		

}).filter('requisfiltre',function(){
	return function(requis,listeRequis){
		requisFiltre=[];
		for (var a=0;a<requis.length;a++){
			if(listeRequis!=null){
			for(var b=0;b<listeRequis.length;b++){
				if(requis[a].id==listeRequis[b].requisId){requisFiltre.push(requis[a].enonce_fr);}
				
			}
			}
			
		}
		return requisFiltre;
	};
}).filter('arenafiltre',function(){
	return function(arenas,listeArenas){
//		console.log("les arenas: "+arenas);
//		console.log("listeArenas: "+listeArenas);
		arenaFiltre=[];
		for (var a=0;a<arenas.length;a++){
		//	for(var b=0;b<listeArenas.length;b++){
				if(arenas[a].arenaId==listeArenas){arenaFiltre.push(arenas[a].nomArena);}
				
		//}
			
		}
		return arenaFiltre;
	};
});
