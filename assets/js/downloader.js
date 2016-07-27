addEventListener('message', function(e) {
	var str = JSON.stringify(e.data.data2);
	var jaxtone = XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	jaxtone.open('POST', e.data.base_url + '/google/save_img', true);
	jaxtone.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	jaxtone.onreadystatechange = function(){ 
		if(jaxtone.readyState==4) {
			try {
				var result = JSON.parse(jaxtone.responseText);
			} catch(err) {
				console.log(err);
				console.log(jaxtone.responseText);
			}
			if (result.resp == 'success'){
				console.log('OK...' + result.map);
				// Send data to parent page
				postMessage({'message' : 'ok na tol'});
			} else {
				console.log('Retrying...');
				setTimeout(function() { DownloadOverallMap(id, polypath); }, 600);
				postMessage({'message' : 'hindi ok tol'});
			}
		}
	}
	jaxtone.send('type=1&data=' + str);
}, false);