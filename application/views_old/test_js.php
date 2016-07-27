var ajax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		var formdata = new FormData();
   		formdata.append('SelectedFile', _file.files[0]);
   		ajax.open('POST',base_url+'/google/add_hazard');
   		ajax.setRequestHeader('Content-Type','application/json');
   		ajax.send(formdata);

		data =  {'location':$("#location_modal").val(),'lat':loc[0].k,'lng':loc[0].A,'title':$('#title_modal').val(),'info':$('#info_modal').val()}
		var str = JSON.stringify(data);
		var jax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		jax.open('POST',base_url+'/google/add_hazard');
		jax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		jax.send('command=save&infodata='+str);
		jax.onreadystatechange = function(){ 
			if(jax.readyState==4) {
				if(jax.responseText == 'success'){
					$('#myModal').modal('hide');
					smoke.signal(jax.responseText+", Redirecting to the list..", function(e){
					setTimeout(function(){window.location=base_url+"/google/hazard_list"},500);
					}, {
						duration: 1500
					});
				}
				else{ 
					console.log(jax.responseText);
				}
			}
		}





		if(isset($_REQUEST['command'])=='save'){
			$data = json_decode($_REQUEST['infodata'],true);
			$today = date("Y-m-d H:i:s"); 
			$info = (empty($data['info']))? 'NULL' : $data['info']; 
			// if(move_uploaded_file($_FILES['SelectedFile']['tmp_name'], 'upload/' . $_FILES['SelectedFile']['name'])){
			//     outputJSON('Error uploading file - check destination is writeable.');
			// }

			var_dump($_SERVER['DOCUMENT_ROOT'].'/googlemaps/uploads/'.basename($data['SelectedFile']['name']));
			exit;
			move_uploaded_file($data['SelectedFile']['name'], $_SERVER['DOCUMENT_ROOT'].'/googlemaps/uploads/'.basename($data['SelectedFile']['name']));
			$this->dan_model->inserting("hazard_tb",array("location"=>"{$data['location']}","latitude"=>"{$data['lat']}","longtitude"=>"{$data['lng']}","title"=>"{$data['title']}","information"=>"{$info}","created_date"=>"{$today}","hazard_image"=>$data['SelectedFile']['name']));
			// $this->db->query("insert into info_tb (location,latitude,longtitude,title,information,created_date) value ('{$data['location']}','{$data['lat']}','{$data['lng']}','{$data['title']}','{$info}','{$today}')");
			die('success');
		}