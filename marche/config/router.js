app.config(function($stateProvider,$urlRouterProvider) {
 
$urlRouterProvider.otherwise("/gardiens");

  $stateProvider.state({
    name: 'joueurs',
    url: '/joueurs',
    templateUrl: '/marche/views/joueurs/joueurs.html'
  }).state({
    name: 'ajouterJoueur',
    url: '/ajouterjoueur',
    templateUrl: '/marche/views/joueurs/ajouterjoueur.html',
    controller: "controleurJoueurs"
  }).state({
    name: 'joueursListe',
    url: '/joueurs/liste',
    templateUrl: '/marche/views/joueurs/liste.html',
    controller: "controleurJoueurs"
  })
  .state('gardiens',{
    url: '/gardiens',
    templateUrl: '/marche/views/gardiens/gardiens.html'
  })
  .state('gardiensListe',{
    url: '/gardiens/liste',
    templateUrl: '/marche/views/gardiens/liste.html',
    controller:  "controleurJoueurs"
  }).state('positions',{
    url: '/positions',
    templateUrl: '/marche/views/positions/positions.html'
  }).state({
    name: 'ajouterPosition',
    url: '/ajouterposition',
    templateUrl: '/marche/views/positions/ajouterposition.html',
    controller: "controleurPositions"
  })
  .state('positionsListe',{
    url: '/positions/liste',
    templateUrl: '/marche/views/positions/liste.html',
    controller:  "controleurPositions"
  })
  .state('arbitres',{
    url: '/arbitres',
    templateUrl: '/marche/views/arbitres/arbitres.html',
    controller:  "controleurArbitres"
  }).state({
    name: 'ajouterArbitre',
    url: '/ajouterarbitre',
    templateUrl: '/marche/views/arbitres/ajouterarbitre.html',
    controller: "controleurArbitres"
  }).state({
    name: 'detailsArbitre',
    url: '/arbitres/:id',
    templateUrl: '/marche/views/arbitres/details.html',
    controller: "controleurArbitres"
  }).state({
    name: 'supprimerArbitre',
    url: '/arbitres/:id',
    templateUrl: '/marche/views/arbitres/supprimer.html',
    controller: "controleurArbitres"
  });
  
});