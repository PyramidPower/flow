<?php 

echo $form;
?>

<script type="text/javascript">



$.ajaxSetup (
		{
			cache: false
		});

var selectURL = "/admin-forms/selectTypesAjax/";

var responseKeep = new Array();

//do ajax call to load post code & suburb information
	$("select[id='module']").change(
		function()
		{
			
			$.getJSON(
					selectURL + $(this).val(),

					function(responseText)
					{
						//alert('sds '+responseText);


						if(responseText)
							{ 		
								
								$('#type').parent().html(responseText); 
							}			
					}
				);

		});

</script>



