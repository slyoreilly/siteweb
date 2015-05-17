
function faitLiens(idActif)
{
document.getElementById('b1l0').onclick=function(){window.location.href="app.html"}
document.getElementById('b1l1').onclick=function(){window.location.href="lesstats.html"}
document.getElementById('b1l2').onclick=function(){window.location.href="siteWeb.html"}
document.getElementById('b1l3').onclick=function(){window.location.href="gestion.html"}

document.getElementById('b2l1').onclick=function(){window.location.href="chandails.html"}
document.getElementById('b2l2').onclick=function(){window.location.href="cellulaires.html"}
document.getElementById('b2l3').onclick=function(){window.location.href="rondelles.html"}
document.getElementById('b2l4').onclick=function(){window.location.href="stats.html"}

if(idActif!=null)
{document.getElementById(idActif).className='btnMenuActif';}
}