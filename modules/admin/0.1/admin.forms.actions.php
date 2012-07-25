<?php


function index_ALL($w)
{
	admin_navigation($w,"PDF Forms");
	
	$w->ctx('newFormButton',Html::box($webroot."/admin-forms/newForm/box","New Form",true));
	
	$formsTable = array(array("Title","Module","Type","Controls"));
	
	$forms = $w->File->getObjects('PdfForm',array('is_deleted'=>0));
	
	if($forms){
		foreach ($forms as $f)
		{
			$line = array();
			$line[] = $f->title;
			$line[] = $f->module;
			$line[] = $f->type;
		
			$controls = Html::box($w->localUrl("/admin-forms/editForm/$f->id"),"Edit",true);
			$controls .= Html::b($w->localUrl("/admin-forms/deleteForm/$f->id"),"Delete","Are you sure you want to delete this record?"); 
		
			$line[] = $controls;
			
			
			$formsTable[] = $line;

		}
	}
	
	$w->ctx("formsTable",Html::table($formsTable,null,"tablesorter",true));
	
	$fieldsArr = $w->Operations->getPdfFields();
	
	$fieldsTable = array(array("Field title","Field description or value set for forms in Operations module"));
	
	
	if($fieldsArr){
		foreach ($fieldsArr as $k=>$v)
		{
			$line = array();
			$line[] = $k;
			$line[] = $v;
				
			
			$fieldsTable[] = $line;

		}
	}
	
	$w->ctx("fieldsTable",Html::table($fieldsTable,null,"tablesorter",true));
	
	$fieldsArr = $w->Sales->getPdfFields();
	
	$fieldsTable = array(array("Field title","Field description or value set for forms in Sales module"));
	
	
	if($fieldsArr){
		foreach ($fieldsArr as $k=>$v)
		{
			$line = array();
			$line[] = $k;
			$line[] = $v;
				
			
			$fieldsTable[] = $line;

		}
	}
	
	$w->ctx("fieldsTableSales",Html::table($fieldsTable,null,"tablesorter",true));
	
	
}


function editForm_GET(Web $w)
{
	$w->setLayout(null);
	
	extract($w->pathMatch('fid'));
	
	$f = $w->auth->getObject('PdfForm', $fid);
	
	if($f->module == 'Operations'){
		$types = $w->Operations->getPdfTypes(); // GC, SHW
	}		
	$form = array(
           array("Form Details","section"),
           array("Title","text","title",$f->title),
           array("Module","text","module",$f->module),
           array("Type","select","type",$f->type,$types),
           //array("Form template file","file","formTemplate"),           
      );

    $form = Html::form($form,$w->localUrl("/admin-forms/editForm/$fid"),"POST","Save", null,null,null,'multipart/form-data'); 
    
	$w->out($form);
	
}


function editForm_POST(Web $w)
{
	extract($w->pathMatch('fid'));
	
	$f = $w->auth->getObject('PdfForm', $fid);
	
	$f->fill($_REQUEST);
	$f->update();
	
	$w->msg("Updated","/admin-forms/index");
}


function newForm_GET(Web $w)
{
	$w->setLayout(null);
	
	// select modules wich support getPdfTypes()
	
	$jobTypes = $w->Operations->getPdfTypes(); // GC, SHW
	$modSelect = array('Operations', 'Sales');
		
	$form = array(
           array("Form Details","section"),
           array("Title","text","title",''),
           array("Module","select","module",null,$modSelect),
          // array("Type","select","type",null,$jobTypes),
           array("Type","select","type"),
           array("Form template file","file","formTemplate"),           
      );

    $form = Html::form($form,$w->localUrl("/admin-forms/newForm/"),"POST","Save", null,null,null,'multipart/form-data'); 
    
	$w->ctx('form',$form);
}


function newForm_POST(Web $w)
{
	if(!($_FILES['formTemplate']['size']>0)){ 
		$msg = "No file uploaded";
	}
	else{
	
		$form = new PdfForm($w);
		$form->fill($_REQUEST);
		
		if($form->module == 'Operations')
		{
			$form->classname = "OpsJob";
		}elseif($form->module == 'Sales'){
			$form->classname = "Form_data_gc";
		}
		$form->insert();
		
		//aDebug($form);exit();
		
		if(!$form->id) $msg = "Form can't be created";
		
		// attach the form template:
		$attach = $w->service("File")->uploadAttachment('formTemplate',$form,null,null); // $description
	    if(!$attach)
	    {
	    	$msg .= "There was an error. Form could not be saved.";
	    }
		
	}
	
	if(!$msg) $msg = "Form template created";
	
	$w->msg($msg,"/admin-forms/index");
}


function deleteForm_GET(Web $w)
{
	extract($w->pathMatch('fid'));
	
	$f = $w->auth->getObject('PdfForm', $fid);
	$f->delete();
	
	$w->msg("Deleted","/admin-forms/index");
}


/*
 * @param fid - id of PdfForm (class is in file model now)
 * @param id of object which data should be used for filling the form
 * */
function fillPdfForm_GET(Web $w){
	
	require_once 'pdftk-php/pdftk-php.php';
	
	$fid = $_REQUEST['fid'];
	$oid = $_REQUEST['oid'];
	
	
	//------------------------------------
	//  get template file and Obj
	//------------------------------------
	if(!$fid || !$oid){  
		$w->error("Form or object can't be found","/admin-forms/index");  
	}
	
	$f = $w->auth->getObject('PdfForm', $fid);
	
	
	
	$attFormTemplate = $w->service("File")->getAttachments('pdf_form', $fid);
	if(!$attFormTemplate){  
		$w->error("Form template attachment can't be loaded","/admin-forms/index");  
	}
	
	// supposed to find only 1 form template file for module.
	$filePdf = FILE_ROOT.$attFormTemplate[0]->fullpath;
	
	if(!file_exists($filePdf)){  
		$w->error("Form template file does not exist","/admin-forms/index");  
	}
	
	
	//--------------------------------------------------
	// get data-fields for Obj to fill in form template 
	//--------------------------------------------------
	$module = $f->module;
	$fields = $w->$module->getPdfData($f->classname,$oid);
	
	//---------------------------------------
	// create filled form:
	//---------------------------------------
	// Empty original forms tenplaits. Only one at the moment:
	$pdf_originals = array();
	$pdf_originals[] = $filePdf;
			
	// Resulting form name:
	$pdfFilledForm = "Form-".$module.".pdf"; 
	
	$fdf_data_names = array(); 
	$fields_hidden = array(); 
	$fields_readonly = array();
				
	$pdfmaker = new pdftk_php();
			
	// try to create filled forms:
	if(!$res = $pdfmaker->createFilledForm($fields,$fdf_data_names,$fields_hidden, $fields_readonly, $pdf_originals, $pdfFilledForm, false, true))
	{
		$w->error("Please check that .pdf form template uploaded and is in correct format.","/admin-forms/index");	
	}else{
		
		//create attachment for object:
		$object = $w->auth->getObject($f->classname,$oid);
		
		$uploaddir = FILE_ROOT. 'attachments/'.$object->getDbTableName().'/'.date('Y/m/d').'/';

		if (!file_exists($uploaddir)) {
			mkdir($uploaddir,0770,true);
		}

		$ident = time();
		$filename = $f->title."_$ident.pdf";
		$uploadfile = $uploaddir . $filename;
		
		if(copy($res, $uploadfile)){

			$filePath = str_replace(FILE_ROOT, "", $uploadfile);
			$att = new Attachment($w);
			$att->filename = $filename;
			$att->fullpath = $filePath;
			$att->parent_table = $object->getDbTableName();
			// invoice id no Job id is kept:
			$att->parent_id = $oid;
			$att->title = "Form_".$oid;
			$att->description = "Form";
			$att->type_code = "Form";
			$att->insert();
			
			// Send a force download header to the browser with a file MIME type and file name passed to the function:			
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename=\"$filename \"");
						
			// get the temp res file to push out:
			readfile($uploadfile);
			// delete the temporary file:
			unlink($res);
		}else{
			$w->error("Error .","/admin-forms/index");
		}	

	}
		

}


/*
 * function is used for newForm - .pdf template uploading.
 * @param module - Operations, Sales
 * OperationsService has a method getPdfTypes().
 * */
function selectTypesAjax_ALL(Web $w){
	
	$w->setLayout(null);
	
	extract($w->pathMatch('module'));
	
	$types = $w->$module->getPdfTypes();
	
	$results = HTML::select('type',$types);
	
	echo json_encode($results);
}



















