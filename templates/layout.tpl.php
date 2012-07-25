<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title><?=ucfirst($w->currentHandler())?><?=$title?' - '.$title:''?></title>
        <?php if($webroot != 'http://flow.pyramidlocal.com.au'):?>
        	<link rel="icon" href="<?=$webroot?>/img/favicon.png" type="image/png"/>
        <?php else:?> 
        	<link rel="icon" href="<?=$webroot?>/img/favicon_local.gif" type="image/png"/>
        <?php endif;?>
        <link rel="stylesheet" type="text/css" href="<?=$webroot?>/css/flow.css" />
        <link rel="stylesheet" type="text/css" href="<?=$webroot?>/css/tablesorter.css" />
        <link rel="stylesheet" type="text/css" href="<?=$webroot?>/css/datePicker.css" />
        

        <!-- link rel="stylesheet" type="text/css" href="<?=$webroot?>/js/jquery-ui-1.8.1/css/ui-lightness/jquery-ui-1.8.1.custom.css" / -->

        
        <link rel="stylesheet" type="text/css" href="<?=$webroot?>/js/jquery-ui-new/css/custom-theme/jquery-ui-1.8.13.custom.css" />
        
        <link rel="stylesheet" type="text/css" href="<?=$webroot?>/css/liveValidation.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="<?=$webroot?>/css/colorbox.css" />
        <link rel="stylesheet" type="text/css" href="<?=$webroot?>/css/jquery.asmselect.css" />
        <script type="text/javascript" src="<?=$webroot?>/js/jquery-1.4.2.min.js" ></script>
        <script type="text/javascript" src="<?=$webroot?>/js/tablesorter/jquery.tablesorter.js"></script>
        <script type="text/javascript" src="<?=$webroot?>/js/tablesorter/addons/pager/jquery.tablesorter.pager.js"></script>
        <script type="text/javascript" src="<?=$webroot?>/js/colorbox/colorbox/jquery.colorbox-min.js"></script>
        
        <!-- script type="text/javascript" src="<?=$webroot?>/js/jquery-ui-1.8.1/js/jquery-ui-1.8.1.custom.min.js"></script -->
        
        <script type="text/javascript" src="<?=$webroot?>/js/jquery-ui-new/js/jquery-ui-1.8.13.custom.min.js"></script>
		<script type="text/javascript" src="<?=$webroot?>/js/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="<?=$webroot?>/js/livevalidation.js"></script>
        <script type="text/javascript" src="<?=$webroot?>/js/flow.js"></script>
        <script type="text/javascript" src="<?=$webroot?>/js/jquery.asmselect.js"></script>
        <script type="text/javascript" src="<?=$webroot?>/js/ckeditor/ckeditor.js"></script>
        <script type="text/javascript" src="<?=$webroot?>/js/boxover.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $(".msg").fadeOut(3000);
                $("table.tablesorter").tablesorter({dateFormat: "uk", widthFixed: true, widgets: ['zebra']});
            });
        </script>

        <?=$htmlheader?>
    </head>
    <body>
        <table width="100%" align="center">
            <tr>
                <td colspan="2">
                    <div id="top-nav">
                    	<?
                    	if ($w->auth->allowed('help/view')) {
                    		$top_navigation[]=Html::box(WEBROOT."/help/view/".$w->_handler.($w->_subhandler ? "-".$w->_subhandler : "")."/".$w->_action,"HELP",false,true,750,600);
                    	}
                    	?>
                        <?=Html::ul($top_navigation,null,"navlinks")?>
                        <?if ($w->auth->allowed('search/results')):?>
	                        <div id="simple-search" style="margin-top: 6px;">
	                            <form action="<?=$webroot?>/search/results" method="get">
	                                <input style="width: 150px;" type="text" name="q" id="q" value="<?=$_REQUEST['q']?>"/>
	                                    <?=Html::select("idx",$w->service('Search')->getSearchIndexes(),$_REQUEST['idx'],null,null,"Search All")?>
	                                <input style="padding-left:15px;padding-right:15px;margin-right:10px" type="submit" value="Surf!"/>
	                            </form>
	                        </div>
                        <?endif;?>
                    </div>
                </td>
            </tr>

            <tr>
                <td valign="top" width="220" >
                	<div class="box">
                        <div class="boxtitle">Pyramid Power Flow</div>
                        <div class="left-nav">
                			<div align="center" style="margin: 15px;">
                				<a href="<?=$webroot?>/main">
                				<img src="<?=$webroot?>/img/logo_top.png" border="0"/>
                				</a>
                			</div>
                		</div>
                	</div>
                    <?if ($navigation):?>
                    <div class="box">
                        <div class="boxtitle"><?=ucfirst($handler)?></div>
                        <div class="left-nav">
                                <?=Html::ul($navigation,null,"navlinks")?>
                        </div>
                    </div>
                    <?endif;?>
                    <?
                    if ($boxes) {
                        foreach ($boxes as $btitle => $box) {
                            ?>
                    <div class="box">
                        <div class="boxtitle"><?=ucfirst($btitle)?></div>
                        <div class="left-nav">
                            <?=$box?>
                        </div>
                        </div>
                            <?
                        }
                    }
                    ?>
                    <?if ($w->auth->user()):?>
                    <div class="box">
                        <div class="boxtitle">Hi, <?=$w->auth->user()->getShortName()?>!</div>
                        <div class="left-nav">
                                <?$n=array(
                                        $w->menuBox("auth/profile/box","Profile"),
                                        $w->menuLink("auth/logout","Logout"),
                                );?>
                                <?=Html::ul($n,null,"navlinks")?>
                        </div>
                    </div>
                    <?endif;?>
                </td>

                <td valign="top" height="100%">
                    <div id="center">
                        <? if ($error):?>
                        <div class="error"><?=$error?></div>
                        <? endif;?>
                        <div id="body">
                        	<span class="content-header"><?=ucfirst($w->currentHandler())?><?=$title?' - '.$title:''?></span>
                        		<?if ($msg): ?><span class="msg"><?=$msg?></span><? endif;?>
                        	<p></p>
                            <?=$body?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2"><div id="footer">Copyright © <a href="http://www.pyramidpower.com.au">Pyramid Power</a> | All Rights Reserved</div></td>
            </tr>
            <tr>
            	<td colspan="2">
            	<center>
            		Pyramid Power Flow is optimised to work with the free <a href="http://www.mozilla.com/en-US/firefox/">Firefox</a> web browser.
            	</center>
            	</td>
            </tr>
        </table>
    </body>
</html>
