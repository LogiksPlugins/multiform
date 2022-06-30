<?php
if(!defined('ROOT')) exit('No direct script access allowed');

foreach($formConfig['fields'] as $a=>$field) {
	if(isset($field['type'])) {
		if(in_array($field['type'], ['file','files','attachment','avatar','photo','photos','image','gallery','jsonfield'])) {
			unset($formConfig['fields'][$a]);
		}
	}
}

$formConfig['actions'] = [
		"submitnew"=> [
			"label"=>"Submit",
			//"class"=>"",
			"type"=>"submit",
			"icon"=>"fa fa-upload",
		],
		"addMoreRows"=> [
			"label"=>"Add More",
			"class"=>"btn btn-addMoreRows btn-default pull-left",
			"type"=>"button",
			"icon"=>"fa fa-plus",
		]
	];

echo '<div class="formbox"><div class="formbox-content">';
echo '<form class="form multiform '.$formConfig['mode'].' '.($formConfig['simpleform']?"simple-form":"").'" method="POST" enctype="multipart/form-data" data-formkey="'.$formConfig["formkey"].'" data-glink="'.$formConfig['gotolink'].'" data-relink="'.$formConfig['reloadlink'].'" data-clink="'.$formConfig['cancellink'].'">';
echo "<div class='form-field-box'>";
echo getFormTable($formConfig['column_width'], $formConfig['fields'],$formData,$formConfig['dbkey'],$formConfig['mode']);	
echo "</div>";
echo '<hr class="hr-normal">';
echo '<div class="form-actions form-actions-padding"><div class="text-right">';
echo getFormActions($formConfig['actions'],$formConfig);
echo '</div></div>';
echo '</form></div></div>';
echo "<script>if(typeof initFormUI=='function') initFormUI(); else $(function() {initFormUI();});</script>";
?>
<style>
.form-group-label {
    margin-bottom: 5px;
}
.btn.btn-addMoreRows {
    margin-left: -20px;
}
.removeRow {
    margin-right: -6px;
    margin-top: -29px;
    cursor: pointer;
}
.form-field-box .row-fields:nth-child(2) .removeRow {
	display: none;
}
</style>
<script>
$(function() {
	$("form.multiform").delegate(".form-field-box .row-fields .removeRow", "click", function(e) {
		if($(this).closest(".row-fields").index()<=1) return;
		$(this).closest(".row-fields").detach();
	})
	$("form.multiform").validate({
		  //debug:true,
		  ignore: ".ignore",
		  errorClass: "error",
		  validClass: "success",
		  //wrapper: "li",
		  //errorContainer: "#messageBox1, #messageBox2",
		  //errorLabelContainer: "#messageBox1 ul",
		  //wrapper: "li",
		  //ignoreTitle: false,
		  //onsubmit: false,
		  //onfocusout: false,
		  //onkeyup: false,
		  //focusCleanup: true,
		  submitHandler: function(form) {
		  	// form.submit();
		  	formKey=$(form).data('formkey');
		  	formFrameID="FORMFRAME"+Math.ceil(Math.random()*10000000);

			$("body").find("iframe.formFrame#"+formFrameID).detach();

		  	$("body").append("<iframe id='"+formFrameID+"' name='"+formFrameID+"' class='formFrame hidden' style='display:none !important;' ></iframe>");
		  	$(form).attr("target",formFrameID);
		  	// $(form).attr("target","_blank");
		  	$(form).attr("action",_service("multiform","submit")+"&formid="+formKey);
		    //$(form).ajaxSubmit();
		    form.submit();

		    $("form[data-formkey='"+formKey+"']").hide();
		    $("form[data-formkey='"+formKey+"']").parent().find(".alert").detach();
		    $("form[data-formkey='"+formKey+"']").parent().append("<div class='ajaxloading ajaxloading3'></div>");
		  },
		  invalidHandler: function(event, validator) {
		  		if(typeof lgksToast=="function") lgksToast("Some required fields are invalid. They have been marked.<br>Please fix them to submit.");
		  		else if(typeof lgksAlert=="function") lgksAlert("Some required fields are invalid. They have been marked.<br>Please fix them to submit.");
		  		else {
		  			alert("Some required fields are invalid. They have been marked.<br>Please fix them to submit.");
		  		}
		  		//console.log(event);
		  		// 'this' refers to the form
			    // var errors = validator.numberOfInvalids();
			    // if (errors) {
			    //   var message = errors == 1
			    //     ? 'You missed 1 field. It has been highlighted'
			    //     : 'You missed ' + errors + ' fields. They have been highlighted';
			    //   $("div.error span").html(message);
			    //   $("div.error").show();
			    // } else {
			    //   $("div.error").hide();
			    // }
		  }
			// ,rules: {
			//     // simple rule, converted to {required:true}
			//     name: "required",
			//     // compound rule
			//     email: {
			//       required: true,
			//       email: true
			//     }
			// }
			// ,messages: {
			//     name: "Please specify your name",
			//     email: {
			//       required: "We need your email address to contact you",
			//       email: "Your email address must be in the format of name@domain.com"
			//     }
			// }
		});
});
function addMoreRows() {
	$(".form-field-box").append($(".formbox .row.row-fields")[0].outerHTML);
	$("input,select,textarea",".formbox .row.row-fields:last-child").each(function() {
	    $(this).attr("id", $(this).attr("id")+"-"+$(".formbox .row.row-fields:last-child").index());
	});
}
</script>