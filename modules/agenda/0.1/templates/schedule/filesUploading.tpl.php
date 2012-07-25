
<h2> test_MultipleFiles </h2>

<form action="" enctype="multipart/form-data" method="POST">

	<!-- Passing files to handleFiles(this.files) -->
	Select images/files: <input type="file" id="input" size="120" multiple="true" value="-" onchange="handleFiles(this.files)" />
					<br/>
					 <input type="text" id="comment" name="comment" value="comment" size='40'/>

	<input type="reset" />

</form>


<div id="dropbox" style="width:300px; height:100px; border: 1px solid green;">Drop files here</div>

<div id="preview" style="width:300px; height:100px; border: 1px solid blue;">Preview</div>


<button onclick="sendFiles();">Upload</button>



<!-- ===================================================================================== -->


<script type="text/javascript">

$.ajaxSetup ({

	cache: false
});

var dropbox;
	 
	dropbox = document.getElementById("dropbox");
	dropbox.addEventListener("dragenter", dragenter, false);
	dropbox.addEventListener("dragover", dragover, false);
	dropbox.addEventListener("drop", drop, false);

	function dragenter(e) {
			  e.stopPropagation();
			  e.preventDefault();
		}
			 
	function dragover(e) {
			  e.stopPropagation();
			  e.preventDefault();
			}
	function drop(e) {
			  e.stopPropagation();
			  e.preventDefault();
			 
			  var dt = e.dataTransfer;
			  var files = dt.files;
			 
			  //Here, we retrieve the dataTransfer field from the event, then pull the file list out of it, 
			  //passing that to handleFiles(). From this point on, handling the files is the same whether the 
			  //user used the input element or drag and drop.
			  handleFiles(files);
			}
	
	
	function handleFiles(files) {
			  for (var i = 0; i < files.length; i++) {
			    var file = files[i];
			    var imageType = /image.*/;
			     
			   // if (!file.type.match(imageType)) {
			  //  continue;
			  //  }
			     
			    //added
			    preview = document.getElementById("preview");
			    
			    //Each image has the CSS class "obj" added to it, to make them easy to find in the DOM tree. 
			    //We also add a file attribute to each image specifying the File for the image; 
			    //this will let us fetch the images for actually uploading later. 
			    var img = document.createElement("img");
			    img.classList.add("obj");
			    img.file = file;
			    img.height = 60;
			    img.width = 60;
			    
			    preview.appendChild(img);
			     
			    
			    var reader = new FileReader();
			    reader.onload = (function(aImg) { return function(e) { aImg.src = e.target.result; }; })(img);
			    reader.readAsDataURL(file);
			  }
			}
	
	
	
	//every thumbnail image is in the CSS class "obj", with the corresponding File attached in a file attribute. 
	function sendFiles() {
		
		var imgs = document.querySelectorAll(".obj");
			   
	
		//-----------------------
		 	var CRLF  = "\r\n";
        	var parts = [];
        	
        	
            var type = "TEXT"; // not in use here.
		
		   // attach images:
		  for (var i = 0; i < imgs.length; i++) {
		    // old:
		    //new FileUpload(imgs[i], imgs[i].file);
		    
		    //-------------------------------------
		    var part = "";
		   
		     var fieldName = imgs[i].file.name;// element.name;
             var fileName  = imgs[i].file.name; // element.files[0].fileName;
             
             part += 'Content-Disposition: form-data; ';
             part += 'name="' + fieldName + '"; ';
             part += 'filename="'+ fileName + '"' + CRLF;
             
             console.log('file: '+imgs[i].file.name);
             
             
             part += "Content-Type: application/octet-stream" + CRLF + CRLF;
             
             /*
              * File contents read as binary data, obviously
              */
             //part += element.files[0].getAsBinary() + CRLF;
             part += imgs[i].file.getAsBinary() + CRLF;
             
             parts.push(part);
             
             
         } // end imgs
         
         
         
         
         part = "";
         comment = document.getElementById("comment");
         part += 'Content-Disposition: form-data; ';
         part += 'name="' + comment.name + '"' + CRLF + CRLF;

         
         part += comment.value + CRLF;
         parts.push(part);
         
         
				//----------------------------------
				// building request body:
				
				var boundary =  "---------------------------" + (new Date).getTime();
				
				  var request = "--" + boundary + CRLF;
		          request+= parts.join("--" + boundary + CRLF);
		          request+= "--" + boundary + "--" + CRLF;
		          
		        // request ready now.  
		        
		          // Ajax request:
		          var xhr  = new XMLHttpRequest;

		          xhr.open("POST", "<?=$webroot.'/agenda-schedule/filesUploadingRes'?>", true); // this.form.action
		          xhr.onreadystatechange = function() {
		              if (xhr.readyState === 4) {
		            	  // show results:
		                  alert("Uploading Results:" + xhr.responseText);
		              }
		          };
		          
		          
		          var contentType = "multipart/form-data; boundary=" + boundary;
		          xhr.setRequestHeader("Content-Type", contentType);

		          for (var header in this.headers) {
		              xhr.setRequestHeader(header, headers[header]);
		          }

		          // finally send the request as binary data
		          xhr.sendAsBinary(request);
		         //-------------------------------------
		         
	}
	

</script>



