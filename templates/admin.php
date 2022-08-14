<?php use WHMCS\Database\Capsule; ?>

<script src="https://www.gstatic.com/firebasejs/8.1.2/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.1.2/firebase-messaging.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">  
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>

<script>
	$(document).ready( function () {
        var uri = new URL(window.location.href);
        var user_id = uri.searchParams.get("user_id");
        if(user_id == null) var user_id = 'none';
        $('#ips').DataTable( {
            "order": [[ 0, "desc" ]],
            paging: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "/modules/addons/g_ip/templates/ajax.php",
                type: "POST",
                data: {"ajax_type": "get_ips", 'user_id': user_id, 'token': '<?= generate_token('plain'); ?>'}
            },
            columns:[
                {data: "id"},
                {data: "user"},
                {data: "ip_address"},
                {data: "request_port"},
                {data: "login_date"},
                {data: "logout_date"},
            ]

        } );
	} );
</script>



<div class="container-fluid">
    <?php
    if($action){
        $alert_type = ($success) ? 'success' : 'danger';
        $title = ($success) ? 'İşlem başarılı!' : 'İşlem başarısız!';
    ?>
       <div class="alert alert-<?= $alert_type ?>">
           <strong>
               <span class="title"><?= $title ?></span>
           </strong>
           <br/>
           <?= $message ?>
       </div>
<?php } ?>
    <div class="row">
        <div style="float: right">
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteRecords">
                <i class="fa fa-trash-alt"></i> Veri Temizle
            </button>
        </div>
    </div>
    <br>
    <div class="row">
        <table id="ips" class="table table-striped table-bordered" style="width:100%">
            <thead>
            <tr>
                <td>#</td>
                <td>Kullanıcı</td>
                <td>IP Adresi</td>
                <td>Request Portu</td>
                <td>Giriş Tarihi</td>
                <td>Çıkış Tarihi</td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>




<div class="modal fade" id="deleteRecords" tabindex="-1" role="dialog" aria-labelledby="deleteRecordsLabel" aria-hidden="true">
    <form method="post" action="<?= $GLOBALS['modulelink'] ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRecordsLabel">Veri Temizle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="dateRecords">Silinecek Veri Tarihi Aralığı</label>
                                <select name="dateRecords" class="form-control" id="dateRecords">
                                    <option value="1">1 aydan eski</option>
                                    <option value="3">3 aydan eski</option>
                                    <option value="6">6 aydan eski</option>
                                    <option value="12">12 aydan eski</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="deleteRecords">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Veri Sil</button>
                </div>
            </div>
        </div>
    </form>
</div>




