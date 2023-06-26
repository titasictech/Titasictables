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
```
