var allVertices;var allHazardsHit=[];var total_distance=0.0;function MyLatLng(lat,lng){this.lat=lat;this.lng=lng;}
function MyHazard(id,title,distance){this.id=id;this.title=title;this.distance=distance;}
if(typeof(Number.prototype.toRad)==="undefined"){Number.prototype.toRad=function(){return this*Math.PI/180;}}
if(typeof(Number.prototype.toDeg)==="undefined"){Number.prototype.toDeg=function(){return this*180/Math.PI;}}
function UpdateAllVertices(polypath){allVertices=[];for(var x=0;x<polypath.length;x++)
{allVertices.push(new MyLatLng(polypath[x].lat,polypath[x].lng));if(x<polypath.length-1)
{var brng=CalculateBearing(polypath[x],polypath[x+1]);var dist=CalculateDistance(polypath[x],polypath[x+1]);GenerateVertices(dist,10,brng,polypath[x]);}}}
function CalculateBearing(latlng1,latlng2)
{var lat1=latlng1.lat.toRad();var lat2=latlng2.lat.toRad();var dLon=(latlng2.lng-latlng1.lng).toRad();var y1=Math.sin(dLon)*Math.cos(lat2);var x1=Math.cos(lat1)*Math.sin(lat2)-
Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);return Math.atan2(y1,x1);}
function GenerateVertices(distance,vertex_space,bearing,latlng1)
{var iterations=Math.floor((distance*1000)/vertex_space);if(vertex_space>=(distance*1000))
return;for(var y=1;y<=iterations;y++)
{var dist=(vertex_space*y)/1000;var R=6371;var d=parseFloat(dist)/R;var lat1=latlng1.lat.toRad(),lon1=latlng1.lng.toRad();var lat2=lat1+d*Math.cos(bearing);var dLat=lat2-lat1;var dPhi=Math.log(Math.tan(lat2/2+Math.PI/4)/Math.tan(lat1/2+Math.PI/4));var q=(Math.abs(dLat)>1e-10)?dLat/dPhi:Math.cos(lat1);var dLon=d*Math.sin(bearing)/q;if(Math.abs(lat2)>Math.PI/2){lat2=lat2>0?Math.PI-lat2:-(Math.PI-lat2);}
var lon2=(lon1+dLon+Math.PI)%(2*Math.PI)-Math.PI;if(isNaN(lat2)||isNaN(lon2)){return null;}
allVertices.push(new MyLatLng(lat2.toDeg(),lon2.toDeg()));}}
function DetectNearbyHazards(allPolyVertices,hazardIds,hazardTitles){var lastPolyHit=null;allHazardsHit=[];total_distance=0.0;for(var i=0;i<allVertices.length;i++)
{if(i>0)
{total_distance+=parseFloat(CalculateDistance(allVertices[i],allVertices[i-1]));}
for(var j=0;j<allPolyVertices.length;j++)
{if(isPointInPoly(allPolyVertices[j],new MyLatLng(allVertices[i].lat,allVertices[i].lng)))
{if(lastPolyHit!=j){allHazardsHit.push(new MyHazard(hazardIds[j],hazardTitles[j],(Math.round((total_distance)*100)/100)));lastPolyHit=j;}
break;}else{if(j==allPolyVertices.length-1){lastPolyHit=null;}}}
total_distance=Math.round((total_distance)*100)/100;}}
function isPointInPoly(poly,pt){var crossings=0;for(var i=0;i<poly.length;i++){var a=poly[i],j=i+1;if(j>=poly.length)
{j=0;}
var b=poly[j];if(rayCrossesSegment(pt,a,b)){crossings++;}}
return(crossings%2==1);}
function rayCrossesSegment(point,a,b)
{var px=point.lng,py=point.lat,ax=a.lng,ay=a.lat,bx=b.lng,by=b.lat;if(ay>by){ax=b.lng;ay=b.lat;bx=a.lng;by=a.lat;}
if(px<0){px+=360};if(ax<0){ax+=360};if(bx<0){bx+=360};if(py==ay||py==by)py+=0.00000001;if((py>by||py<ay)||(px>Math.max(ax,bx)))return false;if(px<Math.min(ax,bx))return true;var red=(ax!=bx)?((by-ay)/(bx-ax)):Infinity;var blue=(ax!=px)?((py-ay)/(px-ax)):Infinity;return(blue>=red);}
function CalculateDistance(p1,p2){var R=6371;var dLat=(p2.lat-p1.lat).toRad();var dLon=(p2.lng-p1.lng).toRad();var lat1=p1.lat.toRad();var lat2=p2.lat.toRad();var a=Math.sin(dLat/2)*Math.sin(dLat/2)+
Math.sin(dLon/2)*Math.sin(dLon/2)*Math.cos(lat1)*Math.cos(lat2);var c=2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));var d=R*c;return d;}
addEventListener('message',function(e){UpdateAllVertices(e.data.polypath);DetectNearbyHazards(e.data.allPolyVertices,e.data.hazardIds,e.data.hazardTitles);var modified=[];for(w=0;w<allVertices.length;w++){if(w%10==0){modified.push(allVertices[w]);}}
postMessage({'allHazardsHit':allHazardsHit,'total_distance':total_distance,'mydata':modified});},false);