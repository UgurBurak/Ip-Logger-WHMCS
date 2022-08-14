<?php


global $CONFIG;
$login_hook = ($CONFIG['Version'][0] >= '8') ? 'UserLogin' : 'ClientLogin';
$logout_hook = ($CONFIG['Version'][0] >= '8') ? 'UserLogout' : 'ClientLogout';
use WHMCS\Database\Capsule;

if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
}

add_hook($login_hook, 1, function($vars){
    if($login_hook == 'UserLogin'){
        $currentUser = new \WHMCS\Authentication\CurrentUser;
        $client = json_decode(json_encode($currentUser->client()));
        $date = new DateTime('NOW');
        if($client->id){
            $_SESSION['g_ip_id'] = Capsule::table('g_ip_last_client_ips')
                ->insertGetId([
                    'client_id' => $client->id,
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'login_date' => $date->format('Y-m-d H:i:s'),
                    'remote_port' => $_SERVER['REMOTE_PORT']
                ]);
        }
    }else{
        $userid = ($vars['userid'] !== NULL) ? $vars['userid'] : 0;
        $_SESSION['g_ip_id'] = Capsule::table('g_ip_last_client_ips')
            ->insertGetId([
                'client_id' => $userid,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'login_date' => $date->format('Y-m-d H:i:s'),
                'remote_port' => $_SERVER['REMOTE_PORT']
            ]);
    }
});

add_hook($logout_hook, 1, function($vars) {
    if($_SESSION['g_ip_id']){
        $update_log = Capsule::table('g_ip_last_client_ips')->where('id' ,$_SESSION['g_ip_id'])->update(['logout_date' => date('Y-m-d H:i:s')]);
        unset($_SESSION['g_ip_id']);
    }
});

add_hook('AdminAreaClientSummaryPage', 1, function($vars) {
	$userid = trim($vars['userid']);
	$get_last_ip_address = Capsule::table('g_ip_last_client_ips')->where('client_id', $userid)->orderBy('login_date', 'desc')->limit(5)->get();
	$box = '';
	$box .= '<div class="clientssummarybox">';
    $box .= '<div class="title">Clients Last IP Address</div>';
    $box .= '<table cellspacing="0" cellpadding="3">';
    $box .= '<thead>';
    $box .= '<tr>';
    $box .= '<th>IP Adresi</th>';
    $box .= '<th>Request Portu</th>';
    $box .= '<th>Tarih</th>';
    $box .= '</tr>';
    $box .= '</thead>';
    if(count($get_last_ip_address) >= 1){
        foreach ($get_last_ip_address as $ip_address) {
            $remote_port = ($ip_address->remote_port != NULL) ? $ip_address->remote_port : 'Yok';
        	$box .= '<tr><td width="110">'.$ip_address->ip_address.'</td><td width="110">'.$remote_port.'</td><td>'.$ip_address->login_date.'</td></tr>';
        }
    }else{
        $box .= '<tr><td align="center">Kayıt Bulunamadı.</td></tr>';
    }    
        $box .= '</table>';
        $box .= '<ul>';
        $box .= '<li><a href="addonmodules.php?module=g_ip&userid='.$ip_address->client_id.'"><img src="images/icons/add.png" border="0" align="absmiddle" /> Daha fazla kayıt gör </a></li>';
        $box .= '</ul>';
        $box .= '</div>';

        $script = '<script>$(document).ready(function(){
            
            $(".client-summary-panels").children().last().append(\'' . $box . '\');
                        
        });</script>';        


    return $script;
});

add_hook('ClientAreaPage', 1, function($vars){
    if($vars['templatefile'] == 'login' || isset($_SESSION['adminid'])) return;
    $currentUser = new \WHMCS\Authentication\CurrentUser;
    $client = $currentUser->client();
    $date = new DateTime('NOW');
    if($vars['loggedin'] === true){
        if(!$_SESSION['g_ip_id']){
            $_SESSION['g_ip_id'] = Capsule::table('g_ip_last_client_ips')
                ->insertGetId([
                    'client_id' => $client->id,
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'login_date' => $date->format('Y-m-d H:i:s'),
                    'remote_port' => $_SERVER['REMOTE_PORT']
                ]);
        }
    }else{
        unset($_SESSION['g_ip_id']);
    }
});