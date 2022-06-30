<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include_once __DIR__."/api.php";

handleActionMethodCalls();

function _service_submit() {
	$formKey=$_REQUEST['formid'];
	if(!isset($_SESSION['FORM'][$formKey])) {
		displayFormMsg("Sorry, form key not found.");
	}

	$formConfig=$_SESSION['FORM'][$formKey];
	processFormHook("dataposted",["config"=>$formConfig,"mode"=>$formConfig['mode']]);
	
	if(!isset($formConfig['source']) || !isset($formConfig['source']['type'])) {
		displayFormMsg("Sorry, Form Submit Source Not Found.");
	}
	
	if(isset($_REQUEST['forsite']) && in_array($_REQUEST['forsite'],$_SESSION['SESS_ACCESS_SITES'])) {
		$fs=_fs($_REQUEST['forsite'],[
				"driver"=>"local",
				"basedir"=>ROOT.APPS_FOLDER.$_REQUEST['forsite']."/".APPS_USERDATA_FOLDER
			]);
	} else {
// 			$fs=_fs(SITENAME,[
// 					"driver"=>"local",
//         	"basedir"=>ROOT.APPS_FOLDER.SITENAME."/".APPS_USERDATA_FOLDER
// 				]);
		$fs=_fs();
		$fs->cd(APPS_USERDATA_FOLDER);
	}

	//printArray($_REQUEST);
	//printArray($_POST);
	// printArray($formConfig);

	// $files=handleFileUpload($formConfig,$fs);

	$source=$formConfig['source'];

	switch ($source['type']) {
		case 'sql':
			$cols=array_keys($formConfig['fields']);
			$where=$source['where'];
			if(!is_array($where)) $where=explode(",", $where);

			// $oriData=$_POST;
			// $oriData=array_merge($formConfig['data'],$oriData);

			$dataInsert = [];
			$totalData = $_POST;
			$_POST = [];

			foreach($cols as $nx=>$key) {
				if(isset($formConfig['fields'][$key]['nofill']) && $formConfig['fields'][$key]['nofill']) {
					unset($cols[$nx]);
					continue;
				}
				if(isset($totalData[$key])) {
					foreach($totalData[$key] as $nx1=>$val) {
						if(!isset($dataInsert[$nx1])) $dataInsert[$nx1] = [];
						$dataInsert[$nx1][$key] = $val;
					}
				}
			}
			if(count($dataInsert)<=0) {
				displayFormMsg("No change found",'info');
			}

			foreach($dataInsert as $key => $row) {
				$oriData = $row;

				//validation
				$row=validateInput($row,$formConfig['fields']);
				$row=processInput($row,$formConfig,$oriData);

				//Merge With fixed data that needs autofilling
				$row=mergeFixedData($row,$formConfig,$oriData);

				if($row) {
					$dataInsert[$key] = $row;
				} else unset($dataInsert[$key]);
			}


			// printArray($cols);	
			// printArray($_POST);
			// printArray($dataInsert);exit();
			//echo _db($dbKey)->_insert_batchQ($source['table'], $dataInsert)->_SQL();

			if(isset($formConfig['slavetable'])) {
				displayFormMsg("Slave tables not supported yet",'info');
			}

			$dbKey=$formConfig['dbkey'];
			switch ($formConfig['mode']) {
				case 'new':
				case 'insert':
					foreach($dataInsert as $key => $row) {
						processFormHook("preSubmit",["config"=>$formConfig,"data"=>$row,"mode"=>"new"]);
					}

					$ans = _db($dbKey)->_insert_batchQ($source['table'], $dataInsert)->_RUN();
					if($ans) {
						//$whereNew=['id'=>_db($dbKey)->get_insertID()];
						//finalizeSubmit($formConfig,$cols,$whereNew);
						
						//$formConfig['mode']="update";
						//$_SESSION['FORM'][$formKey]['mode']="update";
						//$_SESSION['FORM'][$formKey]['source']['where_auto']=$whereNew;

						$_ENV['FORMSUBMIT']=["data"=>$dataInsert,"where"=>[],"config"=>$formConfig,"mode"=>$formConfig['mode']];
						processFormHook("postSubmit",$_ENV['FORMSUBMIT']);
						
						// $_REQUEST['hashid']=md5($whereNew['id']);
						displayFormMsg($cols,'success',"reload");
					} else {
						$msg=_db($dbKey)->get_error();
						$msgMicro=explode(" ", strtolower($msg));
						if($msgMicro[0]=="duplicate") {
							$msgX=$msgMicro[count($msgMicro)-1]." is unique and a record already exists.";
							echo $msg;
							displayFormMsg($msgX,'error');
						} else {
							echo $msg;
							displayFormMsg("Error updating database, try again later",'error');
						}
					}
				break;
				case 'edit':
				case 'update':
					displayFormMsg("Not Supported Yet",'info');
				break;
			}
		break;
		case "php":
			$file=APPROOT.$formConfig['source']['file'];
			if(file_exists($formConfig['source']['file']) && is_file($formConfig['source']['file'])) {
				$data = include_once($formConfig['source']['file']);
				displayFormMsg($data,'success',$formConfig['gotolink']);
			} elseif(file_exists($file) && is_file($file)) {
				$data=include_once($file);
				displayFormMsg($data,'success',$formConfig['gotolink']);
			} else {
				displayFormMsg("Sorry, Form Submit Source File Not Found.");
			}
			break;
		default:
			displayFormMsg("Sorry, Form Source Type Not Supported.");
			break;
	}

}
?>