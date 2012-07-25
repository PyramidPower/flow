<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?=$createForm?>

<script type='text/javascript'>
    CKEDITOR.replace( 'page_body' ,
    {
        toolbar : 'Full'
    });
</script>

<script type="text/javascript" charset="utf-8">
$('#cancel').click(function(){

	history.back();
});
</script>