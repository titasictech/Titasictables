# Titasictables
This is a wrapper class/library inspired and based on Ignited Datatables  found at https://github.com/IgnitedDatatables/Ignited-Datatables for CodeIgniter 3.x

Requirements
---------------------------
CodeIgniter 4.x
jQuery v3.6.x
DataTables 1.13.x

Install
---------------------------
To install the library:
1. copy Titasictech/Titasictables.php to app/ThirdParty
2. open Config/Autoload.php, then add the following code in $psr4
   ```
   public $psr4 = [
     APP_NAMESPACE => APPPATH, // For custom app namespace
        'Config'      => APPPATH . 'Config',
        'Titasictech' => APPPATH . 'ThirdParty/Titasictech'
    ];
   ```
 
Usage
---------------------------
Model
```
<?php namespace App\Models;

use CodeIgniter\Model;
use Titasictech\Titasictables;

class ProgramDocumentModel extends Model {
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
		$datatables->select("a.id, a.program_id, a.document_id, a.step_id, a.is_required, a.effective_date,
							 b.description as program_desc, c.description as document_desc, d.description as step_desc", FALSE) 
					  ->from('program_document a')
					  ->join('program b', 'a.program_id = b.id', 'inner')
					  ->join('document c', 'a.document_id = c.id', 'inner')
					  ->join('step d', 'a.step_id = d.id', 'inner')
					  ->addColumn('actions', 
								'<td>
									<button type="button" id="btn-edit" class="btn btn-sm btn-icon btn-light-secondary mb-0 py-1 px-2" data-mod="$1" data-id="$2" data-desc="$3">
										<span class="btn-inner--icon"><i class="fas fa-edit"></i></span>
										<span class="btn-inner--text">Edit</span>
									</button>
									<button type="button" id="btn-delete" class="btn btn-sm btn-icon btn-danger mb-0 py-1 px-2" data-mod="$1" data-id="$2" data-desc="$3" title="Hapus">
										<span class="btn-inner--icon"><i class="fas fa-trash"></i></span>
										<span class="btn-inner--text">Hapus</span>
									</button>
								</td>',
								'program-document, id, document_desc');  
		echo $datatables->generate();			
	}
}
```
