<?php

if (!function_exists('get_session')) {
	function get_session($data) {
	       $CI =& get_instance();
	       $session = $CI->session->all_userdata();
	       return $session[$data];
	}
}

if (!function_exists('user_info')) {
    function user_info($id,$col) {
           $CI =& get_instance();
           $user_profile = $CI->dan_model->select_where("user_tb",array("user_id"=>$id));
           return $user_profile->$col;
    }
}

if (!function_exists('dump')) {
    function dump ($var, $label = 'Dump', $echo = TRUE)
    {
        $CI =& get_instance();
        // Store dump in variable 
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        
        // Add formatting
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        $output = '<pre style="background: #FFFEEF; color: #000; border: 1px dotted #000; padding: 10px; margin: 10px 0; text-align: left;">' . $label . ' => ' . $output . '</pre>';
        
        // Output
        if ($echo == TRUE) {
            echo $output;
        }
        else {
            return $output;
        }
    }
}
 
if (!function_exists('dump_exit')) {
    function dump_exit($var, $label = 'Dump', $echo = TRUE) {
        $CI =& get_instance();
        dump ($var, $label, $echo);
        exit;
    }
}


if(!function_exists('audit_insert')){
    function audit_insert($desc) {
        $CI =& get_instance();
        $login_info = get_session("login");
        $user_info = $CI->dan_model->select_where("user_tb",array("user_id"=>$login_info['user_id']));
        $CI->dan_model->inserting("audit_trail_tb",array("user_id"=>$user_info->user_id,"description"=>$desc,"firstname"=>$user_info->firstname,"date"=>date("Y-m-d H:i:s"),"email"=>$user_info->email));    
        return TRUE;
    }
}

if(!function_exists('in_array_multi')){
    function in_array_multi($needle, $haystack, $strict = false) {
        foreach ($haystack as $wew => $item) {
            if(in_array($needle,$item)){
                return $wew;
            }
        }
        return false;
    }

}

if(!function_exists("get_user_region")){
    function get_user_region($region) {
        $island = array(
            "Luzon"=>
            array("NCR","CAR","Rehiyong Ilocos","Lambak ng Cagayan","Gitnang Luzon","Calabarzon","MIMAROPA","Kabikulan","Ilocos Region","Cagayan Valley","Central Luzon","CALABARZON","Bicol Region"),
            "Visayas" =>
            array("Kanlurang Kabisayaan","Gitnang Visayas","Silangang Visayas","Western Visayas","Central Visayas","Eastern Visayas"),
            "Mindanao"=>
            array("Tangway ng Kasambuwangaan","Hilagang Mindanao","Rehiyon ng Davao","Caraga","ARMM","Zamboanga Peninsula","Northern Mindanao","Davao Region","SOCCSKSARGEN")
        );
        $get_region = in_array_multi($region,$island);
        return $get_region;
    }
}

