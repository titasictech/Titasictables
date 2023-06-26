# Titasictables
This is a wrapper class/library inspired and based on Ignited Datatables  found at https://github.com/IgnitedDatatables/Ignited-Datatables for CodeIgniter 3.x

Requirements
---------------------------
CodeIgniter 4.x
jQuery v3.6.x
DataTables 1.13.x

Install
---------------------------
To install the library, copy the libraries/datatables.php file into your application/libraries folder.
1. copy Titasictech/Titasictables.php to app/ThirdParty
2. open Config/Autoload.php, then add the following code in $psr4

   ```
   public $psr4 = [
     APP_NAMESPACE => APPPATH, // For custom app namespace
        'Config'      => APPPATH . 'Config',
        'Titasictech' => APPPATH . 'ThirdParty/Titasictech'
    ];
   ```
