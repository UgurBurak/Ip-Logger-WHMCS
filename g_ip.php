<?php
if (!defined("WHMCS")) die("This file cannot be accessed directly");
use WHMCS\Database\Capsule;
use \WHMCS\Application\Support\Controller\DelegationTrait;
use WHMCS\Config\Setting;

function g_ip_config() {
    $config = [
        "name" => "G-IPLogger",
        "description" => "This module can logging client's ip addresses.",
        "version" => "1.1",
        "author" => "<img style='width:80px; height:50px;' src='https://gleox.com/logo/TransparanGLeox4.png' height='150px'>",
    ];
    return $config;
}


function g_ip_activate(){
	try {
	    Capsule::schema()
	    ->create(
	        'g_ip_last_client_ips',
	        function ($table) {
	            $table->increments('id');
	            $table->integer('client_id');
	            $table->text('ip_address');
	            $table->integer('remote_port')->nullable();
	            $table->dateTime('login_date');
                $table->dateTime('logout_date')->nullable();
	        }
	    );
	} catch (\Exception $e) {
	    return [
	        'status' => "error",
	        'description' => $e->getMessage(),
	    ];
	}   
}



function g_ip_deactivate(){
    try {
        Capsule::schema()
            ->dropIfExists('g_ip_last_client_ips');                
        return [
            'status' => 'success',
            'description' => 'Table removing is success.',
        ];
    } catch (\Exception $e) {
        return [
            "status" => "error",
            "description" => "Unable to drop tables: {$e->getMessage()}",
        ];
    }
}


function g_ip_output($vars){
	$modulelink = $vars['modulelink'];
    $userid = $_GET['userid'];
    $requestedAction = $_REQUEST['action'];

    switch ($requestedAction){
        case 'deleteRecords':
            $success = false;
            $message = false;
            $action = $requestedAction;
            try{
                $strtotimes = [
                    '1' => date('Y-m-d H:i:s', strtotime('-1 month')),
                    '3' => date('Y-m-d H:i:s', strtotime('-3 months')),
                    '6' => date('Y-m-d H:i:s', strtotime('-6 months')),
                    '12' => date('Y-m-d H:i:s', strtotime('-12 months'))
                ];
                if(!in_array($_REQUEST['dateRecords'], ["1", "3", "6", "12"])){
                    $message = 'Tarih aralığı geçersiz.';
                }else{
                    $query = Capsule::table('g_ip_last_client_ips')->where('login_date', '<=', $strtotimes[$_REQUEST['dateRecords']]);
                    $counts = $query->count();
                    $query->delete();
                    $success = true;
                    $message = $counts.' adet kayıt başarıyla silindi.';
                }
            }catch (Exception $e){
                $message = $e->getMessage();
            }
    }


    include dirname(__FILE__).'/templates/admin.php';
}
