<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
function customer_index_ALL(Web &$w) {
}


// merging pdf's using pdftk
function customer_mergepdf() {
    $uploaddir = "files/";  //set this to where your files should be uploaded.  Make sure to chmod to 777.

    if ($_FILES['file']) {

        $command = "";

        foreach($_FILES['file']['type'] as $key => $value) {

            $ispdf = end(explode(".",$_FILES['file']['name'][$key]));  //make sure it's a PDF file
            $ispdf = strtolower($ispdf);

            if ($value && $ispdf=='pdf') {
                //upload each file to the server
                $filename = $_FILES['file']['name'][$key];
                $filename = str_replace(" ","",$filename); //remove spaces from file name
                $uploadfile = $uploaddir . $filename;
                move_uploaded_file($_FILES['file']['tmp_name'][$key], $uploadfile);
                //

                //build an array for the command being sent to output the merged PDF using pdftk
                $command = $command." files/".$filename;
                //
            }

        }

        $command = base64_encode($command); //encode and then decode the command string
        $command = base64_decode($command);

        $output = "files/merged-pdf".time().".pdf"; //set name of output file
        $command = "pdftk $command output $output";

        passthru($command); //run the command

        header(sprintf('Location: %s', $output)); //open the merged pdf file in the browser

    }

}
?>