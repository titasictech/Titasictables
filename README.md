# Titasictables
This is a wrapper class/library inspired and based on Ignited Datatables  found at https://github.com/IgnitedDatatables/Ignited-Datatables for CodeIgniter 3.x

Requirements
---------------------------
* CodeIgniter 4.x
* jQuery v3.6.x
* DataTables 1.10+

Install
---------------------------
To install the library:
1. copy Titasictech/Titasictables.php to app/ThirdParty
2. open Config/Autoload.php, then add the following code in $psr4
   ```ruby
   public $psr4 = [
     APP_NAMESPACE => APPPATH, // For custom app namespace
        'Config'      => APPPATH . 'Config',
        'Titasictech' => APPPATH . 'ThirdParty/Titasictech'
    ];
   ```
 
Usage
---------------------------
**Model**
```ruby
<?php namespace App\Models;

use CodeIgniter\Model;
use Titasictech\Titasictables;

class UserModel extends Model {
   	protected $table = 'program_document';
	protected $primaryKey = 'id';
	...
	public function getDatatable($par=null) {
		$datatables = new Titasictables();
		# response if not logged in
		if (!session()->get('is_loggedin')) {
			$output = array(
				'draw' => 0,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => [],
				'csrf_name' => 'X-CSRF-TOKEN', 
				'csrf_content' => $datatables->security->getCSRFHash()
			);
			return json_encode($output);
		}
		# if csrf is enabled in Config/Filters.php then set true
		# if not enabled then set false
		# the function is to insert the csrf token into the response json
		# because if not included, an error will occur when clicking order, search, etc. on jquery datatables
		$datatables->setCSRF(true);
		$datatables->select("a.id, a.user_name, a.full_name, a.description, a.is_active, a.is_login,
							b.description AS level, 
							DATE_FORMAT(a.last_login, '%Y-%m-%d %H:%i') AS last_login", FALSE) 
					->from('user a')
					->join('user_level b', 'a.level_id = b.id', 'inner')
					->addColumn('actions', 
								'<td>
									<button type="button" id="btn-view" class="btn btn-sm btn-icon btn-light-secondary mb-0 py-1 px-2" data-mod="$1" data-id="$2" data-desc="$3">
										<span class="btn-inner--icon"><i class="fa fa-eye"></i></span>
										<span class="btn-inner--text">Detail</span>
									</button>
									<button type="button" id="btn-edit" class="btn btn-sm btn-icon btn-light-secondary mb-0 py-1 px-2" data-mod="$1" data-id="$2" data-desc="$3">
										<span class="btn-inner--icon"><i class="fas fa-edit"></i></span>
										<span class="btn-inner--text">Edit</span>
									</button>
									<button type="button" id="btn-delete" class="btn btn-sm btn-icon btn-danger mb-0 py-1 px-2" data-mod="$1" data-id="$2" data-desc="$3" title="Hapus">
										<span class="btn-inner--icon"><i class="fas fa-trash"></i></span>
									</button>
									<!--<div class="input-group mb-3">-->
										<button type="button" class="btn btn-sm btn-icon btn-light-secondary dropdown-toggle mb-0 py-1 px-2" data-bs-toggle="dropdown" aria-expanded="false">
											<span class="btn-inner--icon"><i class="fa fa-cog"></i></span>
										</button>
										<ul class="dropdown-menu">
											<li><a href="javascript:;" id="btn-reset" class="dropdown-item" data-mod="$1" data-id="$2" data-desc="$3">Reset Password</a></li>
										</ul>
									<!--</div>-->
								</td>',
								'user, id, user_name');  
		echo $datatables->generate();		
	}
}
```
**Controller**
```ruby
<?php namespace App\Controllers;
use App\Models\UserModel;
use CodeIgniter\Config\Services;

class User extends \App\Controllers\MyBaseController {
	private $UserModel;
	public function __construct() {
		parent::__construct();
		$this->UserModel = new UserModel();
	}
	public function getDatatable() {
		return $this->UserModel->getDatatable();
	}
}
```
**View**
```ruby
<!DOCTYPE html>
<html lang="en">
<head>
  <?php
    # generate meta csrf token
    # PHP_EOL sama dengan enter / ln
    echo csrf_meta().PHP_EOL; 
  ?>
</head>
<body>
<div id="user-list" class="row mt-0">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-0">User</h5>
                <p class="text-sm mb-3">User List.</p>
                <a id="btn-add" href="javascript:;" class="btn btn-icon btn-sm btn-danger px-3 mb-0">
                    <span class="btn-inner--icon"><i class="fas fa-plus"></i></span>
                    <span class="btn-inner--text">Add User</span>
                </a>
            </div>
            <div class="card-body table-responsive">
                <table id="dt-user" class="table table-flush nowrap" style="width:100%">
                    <thead class="thead-light">
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Level</th>
                            <th>Status</th>
                            <th>Is Login?</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
            </div>
        </div>
    </div>  
</div>
<script>
    // global variable
    var _csrf_content = $('meta[name="X-CSRF-TOKEN"]').attr('content');
    // Jquery Datatable
    let _start = 0;
    let _limit = 10;
    _sts = '';

    $(function() {
        _datatable = $('#dt-user').DataTable({
            destroy: true,
            processing: true, 
            jQueryUI: false,
            autoWidth: false,
            searching: true,
            paging: true,
            pagingType: 'full_numbers',
            serverSide: true, 
            displayStart: _start,
            pageLength: _limit,
            lengthMenu: [5,10,25,50,100],
            language: {
                /* sudah ada bawaan template
                processing: '<div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">'+
                                '<span class="visually-hidden">Loading...</span>'+
                            '</div>'
                */
                paginate: {
                    "first": "<i class='fas fa-angle-double-left'>",
                    "previous": "<i class='fas fa-angle-left'>",
                    "next": "<i class='fas fa-angle-right'>",
                    "last": "<i class='fas fa-angle-double-right'>"
                },
            },  
            //scrollX" : true,
            //scrollY" : "300",
            scrollCollapse : true,
            responsive: true,
            initComplete: function() {
                //code
            },
            ajax: {
                url: _HOST+'auth/user/get-datatable', 
                type: 'POST',
                data: function (d) {
                    d.csrf_token_name = _csrf_content 
                }
            },
            columnDefs: [
                {
                    targets: [ -1 ], 
                    orderable: false,
                    searchable: false
                },
            ], 
            columns: [
                {data:"user_name", sortable: true, width: "10%", class: "text-sm"},
                {data:"full_name", sortable: true, width: "30%", class: "text-sm"},	
                {data:"level", sortable: true, searchable: false, class: "text-sm"},
                {data:"is_active", sortable: true, searchable: false, class: "text-sm",
                    render: function(data) {
                        let _checked = '';
                        let _label = '';

                        if (data == 'T') {
                            _checked = 'checked';
                            _label = 'Aktive';
                        } else {
                            _label = 'Not Active';
                        }

                        return '<div class="form-check form-switch d-flex align-items-center">'+
                                    '<input class="form-check-input chk-status" type="checkbox" onclick="setActive(this, &apos;'+data+'&apos;, event);" '+_checked+'>'+
                                    '<label class="form-check-label">'+_label+'</label>'+
                                '</div>';
                    }
                },
                {data:"is_login", sortable: true, searchable: false, class: "text-sm",
                    render: function(data) {
                        let _checked = '';
                        let _label = '';

                        if (data == 'T') {
                            _checked = 'checked';
                            _label = 'Yes';
                        } else {
                            _label = 'No';
                        }

                        return '<div class="form-check form-switch d-flex align-items-center">'+
                                    '<input class="form-check-input chk-login" type="checkbox" onclick="setLogin(this, &apos;'+data+'&apos;, event);" '+_checked+'>'+
                                    '<label class="form-check-label">'+_label+'</label>'+
                                '</div>';
                    }
                },
                {data:"actions", sortable: false, width: "20%", class: "text-sm"}	
            ],
            order: [0, 'asc'], 
            drawCallback: function(settings) { 
                // .btn-view, .btn-edit dan .btn-delete declared in [na/getDatatable()
                $(this).on('click', '#btn-view', function(e) {
                    let _id = $(this).data("id");
                    openModal(_HOST+'auth/user/get-form?sts=view&id='+_id, '', 'Detail User', event);
                });
                $(this).on('click', '#btn-edit', function(e) {
                    let _id = $(this).data("id");
                    openModal(_HOST+'auth/user/get-form?sts=edit&id='+_id, '', 'Edit User', event);
                });
                $(this).on('click', '#btn-delete', function(e) {
                    let _id = $(this).data("id");
                    let _desc = $(this).data("desc");
                    // sa special should not be removed
                    if (_desc == 'sa') {swalMsg('Peringatan', 'User Name admin cannot be deleted.', 'warning');return;}
                    let _msg = 'You will delete User Name: ' + _desc + '.';
                    deleteData(_HOST+'auth/user/delete', _id, _msg, event);
                });
                $(this).on('click', '#btn-reset', function(e) {
                    let _id = $(this).data("id");
                    let _desc = $(this).data("desc");
                    let _msg = 'You will reset User Name password: ' + _desc + '.';
                    resetPassword(_HOST+'auth/user/reset-password', _id, _msg, event);
                });
            },
            rowCallback: function(row, data, displayIndex) {

            },
            footerCallback: function (row, data, start, end, display) {

            }
        });

        // Refresh Data
        //_datatable.ajax.reload();

        // to hide datatable error messages, so that they can be seen in the console only
        $.fn.dataTable.ext.errMode = 'throw';
        
        _datatable.on('xhr', function() {
            var _json = _datatable.ajax.json();
            // update csrf token 
    	    updateCSRF(_json.csrf_content);
        });
});

function updateCSRF(_token) {
    // global variable
    _csrf_content = _token;
    // for meta in head (ajax)
    $('meta[name="X-CSRF-TOKEN"]').attr('content', _token);
    // for the input form 
    if (typeof $('input[name="csrf_token_name"]') !== "undefined") {
        $('input[name="csrf_token_name"]').attr('value', _token);
    }
}
</script>
</body>
</html>
```
