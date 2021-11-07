<?php 
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("printMultiform")) {
	
	loadModuleLib("forms", "api");

	function printMultiform($formSrc) {
		if(is_array($formSrc)) $formConfig = $formSrc;
		else $formConfig = findForm($formSrc);

		if(!is_array($formConfig) || count($formConfig)<=2) {
			trigger_logikserror("Corrupt form defination");
			return false;
		}

		// if(!isset($formConfig['multiform'])) {
		// 	trigger_logikserror("Multiform block not found for the report");
		// 	return false;
		// }

		$formConfig['template'] = __DIR__."/templates/form1.php";
		if(!isset($formConfig['column_width'])) $formConfig['column_width'] = 2;

		printForm("new",$formConfig);
		// printArray($formConfig);
	}

	function getFormTable($singleColumnWidth, $fields,$data=[],$dbKey="app",$formMode='new') {
		if(!is_array($fields)) return false;
		//printArray($fields);

		$noLabelContentFields=["widget","source","module","static2","header","avatar"];

		$htmlLabel = "<div class='row'>";
		//$html="<table class='table table-bordered table-striped'>";
		$html="<div class='row row-fields'>";
		foreach ($fields as $field) {
			if(!isset($field['fieldkey'])) {
				continue;
			}
			if(isset($field['policy']) && strlen($field['policy'])>0) {
				$allow=checkUserPolicy($field['policy']);
				if(!$allow) continue;
			}
			
			if(isset($field['form']) && !$field['form']) {
				continue;
			}

			if(!isset($field['important'])) $field['important'] = false;
			if(!isset($field['hide_label'])) {
				if(isset($field['type']) && in_array($field['type'], ["avatar"])) $field['hide_label'] = true;
				else $field['hide_label'] = false;
			}

			if(isset($field['vmode'])) {
				if(!is_array($field['vmode'])) {
					$field['vmode']=explode(",",$field['vmode']);
				}
				if(!in_array($formMode,$field['vmode'])) {
					continue;
				}
			}

			if(!isset($field['label'])) {
				$fieldKey=$field['fieldkey'];
				$field['label']=_ling($fieldKey);
			} else {
				$field['label']=_ling($field['label']);
			}

			if(isset($field['onlyview']) && $field['onlyview']===true) {
				continue;
			}

			//if(!isset($field['width'])) $field['width']=1;
			$field['width']=$singleColumnWidth;

			if(isset($field['hidden']) && $field['hidden']===true) {
				$colClass = "col-xs-12 col-sm-{$field['width']} col-lg-{$field['width']} field-container field-hidden hidden";
			} else {
				$colClass = "col-xs-12 col-sm-{$field['width']} col-lg-{$field['width']} field-container";

				if(isset($field['newline']) && $field['newline']) {
					$colClass .= " col-newline";
				}
				if(isset($field['endofline']) && $field['endofline']) {
					$colClass .= " col-endofline";
				}
				if($field['important']) {
					$colClass .= " col-important";
				}
			}

			if(isset($field['class']) && strlen($field['class'])>0) {
				$colClass .= " {$field['class']}";
			}

			$html.="<div class='{$colClass}'>";
			$htmlLabel.="<div class='{$colClass}'>";

			if(!isset($field['type'])) $field['type']="text";


			if($field['important']) {
				$html.="<div class='form-group important-field'>";
				$htmlLabel.="<div class='form-group-label important-field'>";
			} else {
				$html.="<div class='form-group'>";
				$htmlLabel.="<div class='form-group-label'>";
			}
			
			if(!in_array($field['type'],$noLabelContentFields) && substr($field['label'],0,2)!="__") {
				if($field['hide_label']) $htmlLabel.="<label class='hidden d-none'>{$field['label']}";
				else $htmlLabel.="<label>{$field['label']}";
			} else {
				if($field['hide_label']) $htmlLabel.="<label class='hidden d-none'>";
				else $htmlLabel.="<label>";
			}

			if(isset($field['required']) && $field['required']===true) {
				$htmlLabel.="<span class='span-required'>*</span>";
			}
			if(isset($field['tips']) && strlen($field['tips'])>1) {
				if(substr($field['tips'], 0,7)=="http://" || substr($field['tips'], 0,8)=="https://")
					$htmlLabel.="<a href='{$field['tips']}' target=_blank class='field-tips pull-right fa fa-question-circle'></a>";
				elseif(strlen($field['tips'])<=25)
					$htmlLabel.="<span title='{$field['tips']}' class='field-tips pull-right fa fa-question-circle'> {$field['tips']}</span>";
				else
					$htmlLabel.="<span title='{$field['tips']}' class='field-tips pull-right fa fa-question-circle'> ".substr($field['tips'], 0,20)."...</span>";
			}
			$htmlLabel.="</label></div></div>";
			$field['fieldkey'] = "{$field['fieldkey']}[]";
			$html.=getFormField($field,$data,$dbKey);
			$html.="</div>";
			$html.="</div>";
		}
		$html.="<i class='removeRow fa fa-times pull-right'></i>";
		$html.="</div>";
		// $html.="</div>";
		$htmlLabel.="</div>";

		// return "<div class='container-fluid'>".$htmlLabel.$html."</div>";
		return $htmlLabel.$html;
	}
}
?>