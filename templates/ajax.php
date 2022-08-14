<?php
$dir = explode('modules', dirname(__FILE__));
require_once $dir[0] .'/init.php';
use WHMCS\Database\Capsule;


function getIps(){
    $columns = [
        0 => 'g_ip_last_client_ips.id',
        1 => 'g_ip_last_client_ips.client_id',
        2 => 'g_ip_last_client_ips.ip_address',
        3 => 'g_ip_last_client_ips.remote_port',
        4 => 'g_ip_last_client_ips.login_date',
        5 => 'g_ip_last_client_ips.logout_date'
    ];

    $query = Capsule::table('g_ip_last_client_ips');
    if($_REQUEST['user_id'] != 'none'){
        $query = $query->where('g_ip_last_client_ips.client_id', $_REQUEST['user_id']);
    }
    if($_REQUEST['search']['value']){
        $query = $query->join('tblclients', 'tblclients.id', '=', 'g_ip_last_client_ips.client_id')
            ->where('g_ip_last_client_ips.ip_address', 'like', '%'.$_REQUEST['search']['value'].'%')
            ->orWhere('g_ip_last_client_ips.remote_port', 'like', '%'.$_REQUEST['search']['value'].'%')
            ->orWhere('g_ip_last_client_ips.login_date', 'like', '%'.$_REQUEST['search']['value'].'%')
            ->orWhere('tblclients.id', 'like', '%'.$_REQUEST['search']['value'].'%');

        $val = explode(" ", trim($_REQUEST['search']['value']));
        if(count($val) > 1 && isset($val)){
            $search_surname = $val[array_key_last($val)];
            $search_name = substr($_REQUEST['search']['value'], 0, -strlen($search_surname));
            $query = $query->orWhere('tblclients.firstname', 'like', '%'.$search_name.'%')->orWhere('tblclients.lastname', 'like', $search_surname.'%');
        }else{
            $query = $query->orWhere('tblclients.firstname', 'like', '%'.$_REQUEST['search']['value'].'%')->orWhere('tblclients.lastname', 'like', '%'.$_REQUEST['search']['value'].'%');
        }
    }
    if(isset($_REQUEST['order'][0]['column'])){
        $query = $query->orderBy($columns[$_REQUEST['order'][0]['column']], $_REQUEST['order'][0]['dir']);
    }

    $countAllResults = $query->count();
    if($_REQUEST['start'] > 0){
        $query = $query->offset($_REQUEST['start']);
    }
    if($_REQUEST['length'] > 0) $query = $query->limit($_REQUEST['length']);
    $ips = $query->select('g_ip_last_client_ips.*')->get();
    $results = [];
    $results['data'] = [];
    foreach ($ips as $ip){
        $clientname = Capsule::table('tblclients')->where('id', $ip->client_id)->first();
        $clientname = $clientname->firstname . ' '.$clientname->lastname;
        $remote_port = ($ip->remote_port != NULL) ? $ip->remote_port : 'Yok';
        $logout_date = (empty($ip->logout_date) ? '-' : $ip->logout_date);
        $results['data'][] = [
            'id' => (int)$ip->id,
            'user' => '<a href="clientssummary.php?userid='.$ip->client_id.'">'.$clientname.'</a>',
            'ip_address' => $ip->ip_address,
            'request_port' => $remote_port,
            'login_date' => $ip->login_date,
            'logout_date' => $logout_date
        ];
    }
    $results['draw'] = $_REQUEST['draw'];
    $results['recordsFiltered'] = $results['recordsTotal'] = $countAllResults;
    return $results;
}




if($_SESSION['adminid'] && $_REQUEST['token'] == generate_token('plain')) {
    header("Content-Type: application/json; charset=utf-8");
    switch ($_REQUEST['ajax_type']){
        case 'get_ips':
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(getIps());
            break;
        case 'get_warnings':
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(getWarnings());
            break;
        default:
            echo 'Eksik veri girildi';
            break;
    }
}else{
    exit('Eksik veri girildi.');
}
