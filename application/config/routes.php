<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.

|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "login";
$route['404_override'] = '';

$route['users-list'] = 'login/users_list/'; 
$route['add-user'] = 'login/add_user/'; 
$route['audit-trail'] = 'login/audit_trail'; 
$route['sites'] = 'google/sites';
$route['logout'] = 'login/logout';
$route['routes-list'] = 'google/routes';
$route['hazards-list'] = 'google/hazard_list';
$route['add-route'] = 'google';
$route['route-details/(:num)'] = 'google/fetch_waypoints/$1';
/* Add markers */
$route['add-hazards'] = 'google/add_hazard/h';
$route['add-sites'] = 'google/add_hazard/s';
$route['add-depots'] = 'google/add_hazard/d';
/* EDIT markers */
$route['edit-hazard/(:num)'] = 'google/edit_hazard/h/$1';
$route['edit-site/(:num)'] = 'google/edit_hazard/s/$1';
$route['edit-depot/(:num)'] = 'google/edit_hazard/d/$1';

$route['inactive'] = 'login/inactive';
$route['another_user'] = 'login/another_user';

$route['logs/(:any)/(:num)'] = "google/logs/$1/$1";


/* End of file routes.php */
/* Location: ./application/config/routes.php */