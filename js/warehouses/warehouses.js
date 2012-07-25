
	/**
	 * Ajax calls to select address details;
	 **/
	var url = "/sales-agency/selectSuburbAjax/";

	$.ajaxSetup (
			{
				cache: false
			});

	//do ajax call to load post code & suburb information
	$("#stateAddress option").click(function(){

		if($(this).val())
		{
			$.getJSON(
					url + $(this).val(),

					function(responseText)
					{
            			var option = '<option value="">-- Select --</option>';
            			
                        for(var i in responseText)
                            option += '<option value="' + i +'">' + i +'</option>';

                        $("#suburbAssign").html(option);

                        $("#postCodeAssign").html(null);

                        //add mouse event if user select suburb by clicking the list;
                        $("#suburbAssign option").click(function(){

                			var option = '<option value="">-- Select --</option>';

                			for(var i in responseText[$(this).val()].sort())
            				{
                				if(!$.isFunction(responseText[$(this).val()][i]))
                					option += '<option value="' + responseText[$(this).val()][i] +'">' + responseText[$(this).val()][i] +'</option>';
            				}
                            $("#postCodeAssign").html(option);
                        });

                        //add keyboard event if user select suburb by typing suburb name;
                        $("#suburbAssign").keypress(function(){

                			var option = '<option value="">-- Select --</option>';

                			if(responseText[$(this).val()])
                			{
	                			for(var i in responseText[$(this).val()].sort())
                				{
	                				if(!$.isFunction(responseText[$(this).val()][i]))
	                					option += '<option value="' + responseText[$(this).val()][i] +'">' + responseText[$(this).val()][i] +'</option>';
                				}
	                            $("#postCodeAssign").html(option);
                			}
                        });
					});
		}
	});
	
	/**
	 * Switching tabs
	 **/
	if(!window.location.hash)
		var current_tab = 1;
	else
	{
		var hash = window.location.hash;
		
		var current = hash.split("#");

		var current_tab = current[1];
	}
	
    $('#tab-'+current_tab).show().addClass("active");
    
    $('#tab-link-'+current_tab).addClass("active");
    
    function switchTab(num)
    {
        if (num == current_tab) return;
        
        $('#tab-'+current_tab).hide()
        $('#tab-link-'+current_tab).removeClass("active");
        $('#tab-'+num).show().addClass("active");
        $('#tab-link-'+num).addClass("active");
        
        current_tab = num;
    }