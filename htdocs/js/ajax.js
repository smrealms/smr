var ajaxRunning=true,disableStartAJAX=false,onClickAdded=false;var xmlHttpRefresh,last_refresh_comp=true,intervalRefresh,sn;function startAJAX(){if(disableStartAJAX){return}ajaxRunning=true}function pauseAJAX(){ajaxRunning=false}function stopAJAX(){disableStartAJAX=true;ajaxRunning=false;if(xmlHttpRefresh!==null){xmlHttpRefresh.abort()}}window.onunload=stopAJAX;document.onblur=pauseAJAX;document.onfocus=startAJAX;function addOnClickToLinks(){if(!onClickAdded){onClickAdded=true;var b,a=document.getElementsByTagName("a");for(b=0;b<a.length;b++){a[b].onmouseup=stopAJAX}}}function getXmlHttpObject(){addOnClickToLinks();var a=null;try{a=new XMLHttpRequest()}catch(c){try{a=new ActiveXObject("Msxml2.XMLHTTP")}catch(b){a=new ActiveXObject("Microsoft.XMLHTTP")}}return a}function getURLParameter(f){var g=false,c=window.location.href;if(c.indexOf("?")>-1){var d,e=c.substr(c.indexOf("?")),b=e.split("&");for(d=0;d<b.length;d++){if(b[d].toUpperCase().indexOf(f.toUpperCase()+"=")>-1){var a=b[d].split("=");g=a[1];break}}}return g}function updateRefreshComp(){if(xmlHttpRefresh.readyState==4){var f=xmlHttpRefresh.responseXML,d,b,c,e,a;if(!f||!f.getElementsByTagName("tod")){clearInterval(intervalRefresh);return}c=f.getElementsByTagName("pagecontent")[0].childNodes;for(a=0;a<c.length;a++){d="";for(b=0;b<c[a].childNodes.length;b++){d+=c[a].childNodes[b].nodeValue}document.getElementById(c[a].tagName).innerHTML=d}last_refresh_comp=true}}function updateRefresh(){if(!ajaxRunning||last_refresh_comp===false){return}last_refresh_comp=false;if(xmlHttpRefresh===null){alert("Browser does not support HTTP Request");return}var a="loader.php?sn="+sn+"&ajax=1";xmlHttpRefresh.onreadystatechange=updateRefreshComp;xmlHttpRefresh.open("GET",a,true);xmlHttpRefresh.send(null)}function startRefresh(a){if(!a){return}sn=getURLParameter("sn");if(sn===false){return}xmlHttpRefresh=getXmlHttpObject();intervalRefresh=setInterval(updateRefresh,a)}function toggleWepD(c,d){var a,b;for(b=1;b<=c;b++){if(document.getElementById("wep_item"+b).style.display=="none"){document.getElementById("wep_item"+b).style.display="block"}else{document.getElementById("wep_item"+b).style.display="none"}}a=getXmlHttpObject();a.open("GET",d,true);a.send(null)};