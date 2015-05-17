/*var foo = "bar";
var ob  = {};
ob[foo] = "something"; // === ob.bar = "something"*/

menus= new Array(14);

menus[0]={};
menus[0].id="1_1";
menus[0].lien="/zstats/accueilligue.html";
menus[0].type="n1";
menus[0].iH_fr="Accueil";
menus[0].iH_en="Accueil";
menus[0].keyValue=new Array();menus[0].keyValue[0]={};
menus[0].keyValue[0].ligueId=null;
menus[0].ingredients=new Array();
menus[0].ingredients[0]="m";
menus[0].ingredients[1]="userId";
menus[0].ingredients[2]="ligueId";


menus[1]={};
menus[1].id="1_2";
menus[1].lien="/zuser/monprofil.html";//"/zuser/zuser_accueil.html";
menus[1].type="n1";
menus[1].iH_fr="Zone User";
menus[1].iH_en="User Zone";
menus[1].keyValue=new Array();menus[1].keyValue[0]={};
menus[1].keyValue[0].userId=null;
menus[1].ingredients=new Array();
menus[1].ingredients[0]="userId";

menus[2]={};
menus[2].id="1_3";
menus[2].lien="/zadmin/gestionjoueursligue.html";//"/zadmin/zadmin_accueil.html";
menus[2].type="n1";
menus[2].iH_fr="Zone Admin";
menus[2].iH_en="Admin Zone";
menus[2].keyValue=new Array();menus[2].keyValue[0]={};
menus[2].keyValue[0].ligueId=null;

menus[3]={};
menus[3].id="1_1_1";
menus[3].lien="/zstats/statistiques.html";
menus[3].type="n2";
menus[3].iH_fr="Classement";
menus[3].iH_en="Standings";
menus[3].keyValue=new Array();menus[3].keyValue[0]={};
menus[3].keyValue[0].ligueId=null;
menus[3].ingredients=new Array();
menus[3].ingredients[0]="m";
menus[3].ingredients[1]="userId";
menus[3].ingredients[2]="ligueId";

menus[4]={};
menus[4].id="1_1_2";
menus[4].lien="/zstats/meneurs.html";
menus[4].type="n2";
menus[4].iH_fr="Meneurs";
menus[4].iH_en="Leaders";
menus[4].keyValue=new Array();menus[4].keyValue[0]={};
menus[4].keyValue[0].ligueId=null;
menus[4].ingredients=new Array();
menus[4].ingredients[0]="ligueId";
menus[4].ingredients[1]="m";



menus[5]={};
menus[5].id="1_1_3";
menus[5].lien="/zstats/horaire.html";
menus[5].type="n2";
menus[5].iH_fr="Horaire";
menus[5].iH_en="Schedule";
menus[5].keyValue=new Array();menus[5].keyValue[0]={};
menus[5].keyValue[0].ligueId=null;

menus[6]={};
menus[6].id="1_1_4";
menus[6].lien="/zstats/resultats.html";
menus[6].type="n2";
menus[6].innerHTML="Résultats";
menus[6].iH_fr="Résultats";
menus[6].iH_en="Results";
menus[6].keyValue=new Array();menus[6].keyValue[0]={};
menus[6].keyValue[0].ligueId=null;
menus[6].ingredients=new Array();
menus[6].ingredients[0]="ligueId";
menus[6].ingredients[1]="m";
menus[6].ingredients[2]="userId";

menus[7]={};
menus[7].id="1_2_1";
menus[7].lien="/zuser/monprofil.html";
menus[7].type="n2";
menus[7].iH_fr="Mon profil";
menus[7].iH_en="My profile";
menus[7].keyValue=new Array();menus[7].keyValue[0]={};
menus[7].keyValue[0].userId=null;

menus[8]={};
menus[8].id="1_2_2";
menus[8].lien="/zuser/login.html";
menus[8].type="n2";
menus[8].iH_fr="Connexion";
menus[8].iH_en="Log in";
menus[8].keyValue=new Array();menus[8].keyValue[0]={};
menus[8].keyValue[0].userId=null;

menus[9]={};
menus[9].id="1_1_5";
menus[9].lien="/zstats/equipes.html";
menus[9].type="n2";
menus[9].innerHTML="Équipes";
menus[9].iH_fr="Équipes";
menus[9].iH_en="Teams";
menus[9].keyValue=new Array();menus[9].keyValue[0]={};
menus[9].keyValue[0].ligueId=null;
menus[9].ingredients=new Array();
menus[9].ingredients[0]="m";
menus[9].ingredients[1]="ligueId";
menus[9].ingredients[2]="userId";
menus[9].liensContexte=new Array();
menus[9].liensContexte[0]=0;
menus[9].liensContexte[1]=3;
menus[9].liensContexte[2]=4;
menus[9].liensContexte[3]=6;
menus[9].liensContexte[4]=7;

menus[10]={};
menus[10].id="1_3_1";
menus[10].lien="/zadmin/gestionligue.html";
menus[10].type="n2";
menus[10].innerHTML="Gérer la ligue";
menus[10].iH_fr="Gérer la ligue";
menus[10].iH_en="Manage League";
menus[10].keyValue=new Array();menus[10].keyValue[0]={};
menus[10].keyValue[0].ligueId=null;
menus[10].ingredients=new Array();
menus[10].ingredients[0]="ligueId";
menus[10].ingredients[1]="userId";

menus[11]={};
menus[11].id="1_3_2";
menus[11].lien="/zadmin/gestionjoueursligue.html";
menus[11].type="n2";
menus[11].innerHTML="Gérer les joueurs";
menus[11].iH_fr="Gérer les joueurs";
menus[11].iH_en="Manage Players";
menus[11].keyValue=new Array();menus[11].keyValue[0]={};
menus[11].keyValue[0].ligueId=null;

menus[12]={};
menus[12].id="1_3_3";
menus[12].lien="/zadmin/zeroconfig.html";
menus[12].type="n2";
menus[12].innerHTML="Préparer un match";
menus[12].iH_fr="Préparer un match";
menus[12].iH_en="Prepare a Game";
menus[12].liensAmis=new Array();
menus[12].liensAmis[0]=5;
menus[12].liensAmis[1]=14;
menus[12].keyValue=new Array();menus[12].keyValue[0]={};
menus[12].keyValue[0].ligueId=null;
menus[12].ingredients=new Array();
menus[12].ingredients[0]="m";
menus[12].ingredients[1]="ligueId";
menus[12].liensContexte=new Array();
menus[12].liensContexte[0]=0;
menus[12].liensContexte[1]=3;
menus[12].liensContexte[2]=4;
menus[12].liensContexte[3]=6;
menus[12].liensContexte[4]=7;

menus[13]={};
menus[13].id="1_4";
menus[13].lien="/zdoc/videos.html";
menus[13].type="n1";
menus[13].innerHTML="Infos";
menus[13].iH_fr="Infos";
menus[13].iH_en="Infos";
menus[13].keyValue=new Array();

menus[14]={};
menus[14].id="2_1";
menus[14].lien="/admin/entreprofil.html?code=40";
menus[14].type="n10";
menus[14].innerHTML="Créer un joueur";
menus[14].iH_fr="Créer un joueur";
menus[14].iH_en="Create a Player";
menus[14].keyValue=new Array();

menus[15]={};
menus[15].id="1_4_1";
menus[15].lien="/zdoc/faq.html";
menus[15].type="n2";
menus[15].innerHTML="FAQ";
menus[15].iH_fr="FAQ";
menus[15].iH_en="FAQ";
menus[15].keyValue=new Array();

menus[16]={};
menus[16].id="1_4_2";
menus[16].lien="/zdoc/videos.html";
menus[16].type="n2";
menus[16].innerHTML="Vidéos";
menus[16].iH_fr="Vidéos";
menus[16].iH_en="Videos";
menus[16].keyValue=new Array();

menus[17]={};
menus[17].id="1_4_3";
menus[17].lien="/zdoc/savoirplus.html";
menus[17].type="n1";
menus[17].innerHTML="Pour en savoir plus";
menus[17].iH_fr="Pour en savoir plus";
menus[17].iH_en="Tell me more";
menus[17].keyValue=new Array();


menus[18]={};
menus[18].id="2_2";
menus[18].lien="/zadmin/creerligue.html";
menus[18].type="n10";
menus[18].innerHTML="Créer une ligue";
menus[18].iH_fr="Créer une ligue";
menus[18].iH_en="Create a league";
menus[18].keyValue=new Array();

menus[19]={};
menus[19].id="1_4_4";
menus[19].lien="/zdoc/demarrer.html";
menus[19].type="n2";
menus[19].innerHTML="Démarrer avec SyncStats";
menus[19].iH_fr="Démarrer avec SyncStats";
menus[19].iH_en="Start with SyncStats";
menus[19].keyValue=new Array();


menus[20]={};
menus[20].id="1_3_5";
menus[20].lien="/zadmin/gestionarenas.html";
menus[20].type="n2";
menus[20].innerHTML="Gestion des arénas";
menus[20].iH_fr="Gestion des arénas";
menus[20].iH_en="Arenas management";
menus[20].keyValue=new Array();

menus[21]={};
menus[21].id="1_1_5";
menus[21].lien="/zstats/galerie.html";
menus[21].type="n1";
menus[21].innerHTML="Galerie de photo";
menus[21].iH_fr="Galerie de photo";
menus[21].iH_en="Photo gallery";
menus[21].keyValue=new Array();

menus[22]={};
menus[22].id="10_0";
menus[22].lien="/zstats/match.html";
menus[22].type="n10";
menus[22].innerHTML="Match";
menus[22].iH_fr="Match";
menus[22].iH_en="Game";
menus[22].keyValue=new Array();
menus[22].ingredients=new Array();
menus[22].ingredients[0]="m";

menus[23]={};
menus[23].id="10_0";
menus[23].lien="/zarbitre/listeArbitres.html";
menus[23].type="n2";
menus[23].innerHTML="Chercher un arbitre";
menus[23].iH_fr="Chercher un arbitre";
menus[23].iH_en="Look for a referee";
menus[23].keyValue=new Array();

menus[24]={};
menus[24].id="10_1";
menus[24].lien="/zarbitre/profilArbitre.html";
menus[24].type="n10";
menus[24].innerHTML="Pofil d'arbitre";
menus[24].iH_fr="Pofil d'arbitre";
menus[24].iH_en="Referee Profile";
menus[24].keyValue=new Array();


menus[25]={};
menus[25].id="10_2";
menus[25].lien="/zstats/rapportmatch.html";
menus[25].type="n10";
menus[25].innerHTML="Rapport de match";
menus[25].iH_fr="Rapport de match";
menus[25].iH_en="Game Report";
menus[25].keyValue=new Array();
menus[25].ingredients=new Array();


menus[26]={};
menus[26].id="10_3";
menus[26].lien="/zstats/statsjoueur.html";
menus[26].type="n10";
menus[26].innerHTML="Statistiques du joueur";
menus[26].iH_fr="Statistiques du joueur";
menus[26].iH_en="Player stats";
menus[26].keyValue=new Array();
menus[26].ingredients=new Array();
menus[26].ingredients[0]="m";
menus[26].ingredients[1]="ligueId";
menus[26].ingredients[2]="userId";
menus[26].liensContexte=new Array();
menus[26].liensContexte[0]=0;
menus[26].liensContexte[1]=3;
menus[26].liensContexte[2]=4;
menus[26].liensContexte[3]=6;
menus[26].liensContexte[4]=7;


menus[27]={};
menus[27].id="10_3";
menus[27].lien="/syncboard.html";
menus[27].type="n10";
menus[27].innerHTML="SyncBoard";
menus[27].iH_fr="SyncBoard";
menus[27].iH_en="SyncBoard";
menus[27].keyValue=new Array();
menus[27].ingredients=new Array();
menus[27].ingredients[0]="m";
menus[27].ingredients[1]="ligueId";
menus[27].ingredients[2]="userId";

menus[28]={};
menus[28].id="1_5";
menus[28].lien="/produits.html";
menus[28].type="n1";
menus[28].innerHTML="Produits";
menus[28].iH_fr="Produits";
menus[28].iH_en="Products";

menus[29]={};
menus[29].id="1_1";
menus[29].lien="/zstats/accueilligue.html";
menus[29].type="n1";
menus[29].iH_fr="Stats";
menus[29].iH_en="Stats";
menus[29].keyValue=new Array();menus[29].keyValue[0]={};
menus[29].keyValue[0].ligueId=null;
menus[29].ingredients=new Array();
menus[29].ingredients[0]="m";
menus[29].ingredients[1]="userId";
menus[29].ingredients[2]="ligueId";

menus[30]={};
menus[30].id="1_2_2";
menus[30].lien="/zuser/login.html";
menus[30].type="n2";
menus[30].iH_fr="Connexion";
menus[30].iH_en="Log in";
menus[30].keyValue=new Array();menus[30].keyValue[0]={};
menus[30].keyValue[0].userId=null;
menus[30].ingredients=new Array();
menus[30].ingredients[0]="m";
menus[30].ingredients[1]="userId";


hierarchie= new Array(3);
for(j=0;j<hierarchie.length;j++)
{hierarchie[j]=new Array();}

hierarchie[0][0] = 29;//"1_1";
hierarchie[0][1] = 0;//"1_1";
hierarchie[0][2]=3;//"1_1_1";
hierarchie[0][3]=4;//"1_1_2";
//hierarchie[0][4]=5;//"1_1_3";
hierarchie[0][4]=6;//"1_1_4";
hierarchie[0][5]=7;//"1_1_4";
//hierarchie[0][6]=21;//"1_1_4";
/*hierarchie[1][0] =1;// "1_2";
hierarchie[1][2] =7;// "1_2_1";
hierarchie[1][1] =8;// "1_2_2";
hierarchie[2][0] =2; //"1_3";
hierarchie[2][1] =10; //"1_3_1";
hierarchie[2][2] =11; //"1_3_2";
hierarchie[2][3] =12; //"1_3_3";*/
hierarchie[1][0] =13; //"1_4";
hierarchie[1][1] =13; //"1_4";
hierarchie[1][4] =15; //"1_4";
hierarchie[1][2] =16; //"1_4";
//hierarchie[2][4] =23; //"1_4";
//hierarchie[0][6] =17; //"1_4";
hierarchie[1][3] =19; //"1_4";
//hierarchie[0][5]=9;//"1_1_5";
//hierarchie[0][7]=20;//"1_3_5";
hierarchie[2][0] =28; //"1_4";
