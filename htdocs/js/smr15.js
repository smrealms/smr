!function(){"use strict"
var t,n,o,e,a,c,l
window.voteSite=function(t,n){window.open(t),window.location=n},e=function(t,n,o){for(var e=1,a=0,c=document.FORM;n>=e;e++)a+=1*c[t+e].value
c[o].value=a},a=function(){e("port",9,"total")},window.startCalc=function(){t=setInterval(a,10)},window.stopCalc=function(){clearInterval(t)},c=function(){e("mine",20,"totalM")},window.startCalcM=function(){n=setInterval(c,10)},window.stopCalcM=function(){clearInterval(n)},window.setEven=function(){var t=2,n=document.FORM
for(n.race1.value=12;9>=t;t++)n["race"+t].value=11
n.racedist.value=100},l=function(){e("race",9,"racedist")},window.startRaceCalc=function(){o=setInterval(l,10)},window.stopRaceCalc=function(){clearInterval(o)}
var r,i,u,d,s,w,v,f=!1
w=function(){clearInterval(d),r.style.backgroundColor=u,f=!1},v=function(){var t=document.getElementsByTagName("body")[0]
t.style.backgroundColor=t.style.backgroundColor===u?i:u},window.triggerAttackBlink=function(t){null==u&&(u=document.getElementsByTagName("body")[0].style.backgroundColor),i="#"+t,clearTimeout(s),f===!1&&(f=!0,v(),d=setInterval(v,500)),s=setTimeout(w,3500)}}()