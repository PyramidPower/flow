<?php
// $Id: html.php 887 2010-11-15 09:57:28Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

class Html {

    /**
     * Creates an html table from an array like
     * (
     *   ("one","two","three"),
     *	 ("hello","world","bla")
     * )
     *
     * @param array $array is the array of data
     * @param string $id is a css id
     * @param string $class is a css class
     * @param boolean $header use first row as <th> if true
     *
     */
    function table(&$array,$id=null,$class=null,$header=null) {
        if (!$array || sizeof($array) < 1) return "";

        $jstable = "table".($class?".".$class:"");
        $id = $id ? ' id="'.$id.'"' : null;
        $class = $class ? ' class="'.$class.'"' : null;

        $buf = "<table border='0' ".$id.$class.">\n";
        $firstline = true;
        foreach ($array as $line) {
            // check if this is header line
            $ct = "td";
            if ($firstline) {
                foreach ($line as $cell) {
                    $buf.="<colgroup></colgroup>";
                }
                $buf.="<thead>\n";
                $ct = $header ? "th" : $ct;
            }
            $buf.="<tr>\n";
            foreach ($line as $cell) {
                $buf.="<$ct>$cell</$ct>";
            }
            $buf.="\n</tr>\n";
            if ($firstline) {
                $buf.="</thead>\n<tbody>\n";
                $firstline = false;
            }
        }
        $buf .= "</tbody>\n</table>\n";
        return $buf;
    }

    /**
     * creates a html link
     */
    function a($href,$title,$alt=null,$class=null,$confirm=null,$target=null) {
        if ($confirm) {
            $confirm = " onclick=\"javascript:return confirm('".$confirm."');\" ";
        }
        if ($target) {
            $target = " target='$target' ";
        }
        return '<a href="'.$href.'" alt="'.$alt.'" class="'.$class.'"'.$confirm.$target.'>'.$title.'</a>';
    }

    function b($href,$title,$confirm=null,$id=null) {
        if ($confirm) {
            $js = "if(confirm('".$confirm."'))";
        }        
        $js .= "{ parent.location='".$href."'; return false;}";
        return "<button id='".$id."' onclick=\"".$js."\">".$title."</button>";
    }
    /**
     * Creates a link (or button) which will pop up a colorbox
     * containing the contents of the url
     *
     * @param <type> $href   (M) the url to display in the colorbox
     * @param <type> $title  (M) the link title
     * @param <type> $button (O) if true create a buttin instead of a link
     * @param <type> $iframe (O) whether to use an iframe to display the html contents (default: false)
     */
    function box($href,$title,$button=false,$iframe=false,$width=null,$height=null) {
        $onclick = Html::boxOnClick($href,$iframe,$width,$height);
        $tag = "a";
        if ($button) {
            $tag = "button";
        }
        return "<".$tag.$onclick.">".$title."</".$tag.">";
    }

    function boxOnClick($href,$iframe=false,$width=null,$height=null) {
        if ($iframe) {
            $width = ", innerWidth:".$width;
            $height = ", innerHeight:".$height;
        }
        $iframe = $iframe ? "true" : "false";
        return " onclick=\"$.fn.colorbox({transition:'elastic', href:'".$href."', iframe:".$iframe.$width.$height."});return false;\" ";
    }
    /**
     * creates a ul from an array structure:
     * ("1","2", array("2.1","2.2"),"3")
     */
    function ul(& $array, $id=null, $class=null, $subclass=null, $type="ul") {
        if (!$array || sizeof($array) < 1) return "";

        $id = $id ? ' id="'.$id.'"' : null;
        $class = $class ? ' class="'.$class.'"' : null;
        $buf = "<{$type}".$id.$class.">\n";
        for ($i = 0;$i < sizeof($array); $i++) {
            $cur = $array[$i];
            $next = $i < sizeof($array)-1 ? $array[$i+1] : null;
            $buf .= "<li>".$cur;
            if (is_array($next)) {
                $buf.= $this->ul($next,null,$subclass);
            }
            $buf .="</li>\n";
        }
        $buf .= "</{$type}>\n";
        return $buf;
    }

    /**
     * creates a ol from an array structure:
     * ("1","2", array("2.1","2.2"),"3")
     */
    function ol(& $array, $id=null, $class=null, $subclass=null) {
        return ul($array, $id, $class, $subclass, "ol");
    }
    /**
     * creates a simple one column form from the following array:
     * array(
     * 		array("title","type","fieldname","value",{size | array(select options) | cols, rows}),
     *  	...
     * )
     *
     * valid field types are:
     *  text, password, autocomplete, static, date, textarea, section,
     *  select, multiselect, checkbox, hidden
     *
     * when prefixing a fieldname with a minus sign '-' this field will be read-only
     */
    function form($data, $action=null, $method="POST", $submitTitle="Save", $id=null, $class=null, $target="_self", $enctype = null) {
        if (!$data) return;
        $hidden = "";
        $id = $id ? ' id="'.$id.'"' : null;
        $class = $class ? ' class="'.$class.'"' : null;
        $enctype = $enctype ? " enctype='".$enctype."' " : "";
        $buf = '<form'.$id.$class.$enctype.' action="'.$action.'" method="'.$method.'" target="'.$target.'">'."<table class='form'>\n";
        $valign = ' valign="top" ';
        foreach ($data as $row) {
            $title = $row[0];
            $type = $row[1];
            $name = $row[2];
            $value = $row[3];
            $readonly = "";
            // handle disabled fields
            if ($name[0]=='-') {
                $name = substr($name, 1);
                $readonly = " readonly='true' ";
            }
            // span entry fields that have no title
            if (!$title) {
                $colspan=2;
            } else {
                $colspan=1;
            }
            if ($type == "text" || $type == "password") {
                $size = $row[4];
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
                $buf .= "<td colspan=\"$colspan\">";
                $buf .= '<input'.$readonly.' style="width:100%;"  type="'.$type.'" name="'.$name.'" value="'.htmlspecialchars($value).'" size="'.$size.'" id="'.$name.'"/>';
                $buf .= "</td></tr>\n";
            } else if ($type == "autocomplete") {
                $options = $row[4];
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
                $buf .= "<td colspan=\"$colspan\">";
                $buf.= Html::autocomplete($name,$options,$value,null,"width: 100%;");
                $buf .= "</td></tr>\n";
            } else if ($type == "date") {
                $size = $row[4];
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
                $buf .= "<td colspan=\"$colspan\">";
                $buf .= Html::datePicker($name,$value,$size);
                $buf .= "</td></tr>\n";
            } else if ($type == "datetime") {
                $size = $row[4];
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
                $buf .= "<td colspan=\"$colspan\">";
                $buf .= Html::datetimePicker($name,$value,$size);
                $buf .= "</td></tr>\n";
            } else if ($type == "time") {
                $size = $row[4];
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
                $buf .= "<td colspan=\"$colspan\">";
                $buf .= Html::timePicker($name,$value,$size);
                $buf .= "</td></tr>\n";
            } else if ($type == "static") {
                $size = $row[4];
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
                $buf .= "<td colspan=\"$colspan\">";
                $buf .= $value;
                $buf .= "</td></tr>\n";
            } else if ($type == "textarea") {
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
                $buf .= "<td colspan=\"$colspan\">";
                $cols = $row[4];
                $rows = $row[5];
                $buf .= '<textarea name="'.$name.'" rows="'.$rows.'" cols="'.$cols.'" id="'.$name.'">'.$value.'</textarea>';
                $buf .= "</td></tr>\n";
            } else if ($type == "section") {
                $buf .= '<tr><td colspan="2" class="section" >'.htmlentities($title);
                $buf .= "</td></tr>\n";
            } else if ($type == "select") {
                $items = $row[4];
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
                $buf .= "<td colspan=\"$colspan\">";                if ($readonly == ""){
                    $buf.= Html::select($name,$items,$value);
                } else {
                    $buf.=$value;
                }
                $buf .= "</td></tr>\n";
            } else if ($type == "multiSelect") {
                $items = $field[4];
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
                $buf .= "<td colspan=\"$colspan\">";
                if ($readonly == ""){
                    $buf.= Html::multiSelect($name,$items,$value,null,"width: 100%;");
                } else {
                    $buf.=$value;
                }
                $buf .= "</td>\n";
            } else if ($type == "checkbox") {
                $checked = $value == "1" ? 'checked = "checked"' : "";
                $buf.= "<tr><td  $valign class='fieldtitle'>".htmlentities($title)."</td><td $valign >";
                $buf.= "<input type=\"checkbox\" name=\"$name\" value=\"1\" $checked id='".$name."'>";
                $buf .= "</td></tr>\n";
            } else if ($type == "hidden") {
                $hidden .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'"/>'."\n";
            } else if ($type == "file") {
                $size = $row[4];
                if ($title){
                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
                }
            	$buf .= "<td colspan=\"$colspan\">";
                $buf .= '<input style="width:100%;"  type="'.$type.'" name="'.$name.'" size="'.$size.'" id="'.$name.'"/>';
                $buf .= "</td></tr>\n";
            }
        }
        if ($action) {
            $buf .= '<tr><td colspan="2" align="right"><input type="submit" value="'.$submitTitle.'"/></td></tr>';
        }
        $buf .= "</table>\n";
        $buf .= $hidden."</form>\n";
        return $buf;
    }

    function datePicker($name,$value=null,$size=null) {
        $buf = '<input class="date_picker" type="text" name="'.$name.'" value="'.$value.'" size="'.$size.'" id="'.$name.'"/>';
        $buf.= "<script>$('#$name').datepicker({dateFormat: 'dd/mm/yy'});$('#$name').keyup( function(event) { $(this).val('');}); </script>";
        return $buf;
    }

    function datetimePicker($name,$value=null,$size=null) {
        $buf = '<input class="date_picker" type="text" name="'.$name.'" value="'.$value.'" size="'.$size.'" id="'.$name.'"/>';
        $buf.= "<script>$('#$name').datetimepicker({ampm: true, dateFormat: 'dd/mm/yy'});$('#$name').keyup( function(event) { $(this).val('');}); </script>";
        return $buf;
    }
    
    function timePicker($name,$value,$size) {
        $buf = '<input class="date_picker" type="text" name="'.$name.'" value="'.$value.'" size="'.$size.'" id="'.$name.'"/>';
        $buf.= "<script>$('#$name').timepicker({ampm: true, dateFormat: 'dd/mm/yy'});$('#$name').keyup( function(event) { $(this).val('');}); </script>";
        return $buf;
    }
    
    /**
     * Creates a complex form where each section can have
     * a different number of columns.
     * 
     * extrabuttons = array("id"=>"title", ..)
     *
     * valid field types are:
     *  text, password, autocomplete, static, date, textarea, section,
     *  select, multiselect, checkbox, hidden
     *
     * when prefixing a fieldname with a minus sign '-' this field will be read-only
     *
     * @param <type> $data
     * @param <type> $action
     * @param <type> $method
     * @param <type> $submitTitle
     * @param <type> $id
     * @param <type> $class
     * @param <type> $extrabuttons
     * @return <type>
     */
    function multiColForm($data, $action=null, $method="POST", $submitTitle="Save", $id=null, $class=null,$extrabuttons=null, $target="_self", $enctype = null) {
        if (!$data) return;
        $hidden = "";
        $id = $id ? ' id="'.$id.'"' : null;
        $class = $class ? ' class="'.$class.'"' : null;
        $buf = '<form'.$id.$class.' action="'.$action.'" method="'.$method.'" target="'.$target.'">'."<table class='form-wrapper'>\n";
        $valign = ' valign="top" ';
        foreach ($data as $section => $rows) {
            $buf .= "<tr>";
            $buf .= '<td class="section" >'.htmlentities($section);
            $buf .= "</td></tr>\n<tr><td><table class='form-section' width='100%'>";
            foreach ($rows as $row) {
                $buf .= "<tr>";
                foreach ($row as $field) {
                    $title = $field[0];
                    $type = $field[1];
                    $name = $field[2];
                    $value = $field[3];
                    $readonly = "";
                    // handle disabled fields
                    if ($name[0]=='-') {
                        $name = substr($name, 1);
                        $readonly = " readonly='true' ";
                    }
                    if ($type == "text" || $type == "password") {
                        $size = $field[4];
                        $buf .= "<td $valign class='fieldtitle'>$title</td><td $valign  class='fieldvalue'>";
                        $buf .= '<input'.$readonly.' style="width:100%;" type="'.$type.'" name="'.$name.'" value="'.htmlspecialchars($value).'" size="'.$size.'" id="'.$name.'"/>';
                        $buf .= "</td>\n";
                    } else if ($type == "autocomplete") {
                        $options = $field[4];
                        $buf.= "<td  $valign class='fieldtitle'>".htmlentities($title)."</td><td  $valign class='fieldvalue'>";
                        $buf.= Html::autocomplete($name,$options,$value,null,"width: 100%;");
                        $buf .= "</td>\n";
                    } else if ($type == "date") {
                        $size = $field[4];
                        $buf .= "<td $valign class='fieldtitle'>$title</td><td  $valign class='fieldvalue'>";
                        $buf .= Html::datePicker($name,$value,$size);
                        $buf .= "</td>\n";
                    } else if ($type == "datetime") {
                        $size = $field[4];
                        $buf .= "<td $valign class='fieldtitle'>$title</td><td  $valign class='fieldvalue'>";
                        $buf .= Html::datetimePicker($name,$value,$size);
                        $buf .= "</td>\n";
                    } else if ($type == "time") {
                        $size = $field[4];
                        $buf .= "<td $valign class='fieldtitle'>$title</td><td  $valign class='fieldvalue'>";
                        $buf .= Html::timePicker($name,$value,$size);
                        $buf .= "</td>\n";
                    } else if ($type == "static") {
                        $size = $field[4];
                        $buf .= "<td $valign class='fieldtitle'>$title</td><td  $valign class='fieldvalue'>";
                        $buf .= $value;
                        $buf .= "</td>\n";
                    } else if ($type == "textarea") {
                        $buf .= "<td $valign class='fieldtitle'>$title</td><td  $valign class='fieldvalue'>";
                        $c = $field[4];
                        $r = $field[5];
                        $buf .= '<textarea'.$readonly.' style="width:100%;" name="'.$name.'" rows="'.$r.'" cols="'.$c.'" id="'.$name.'">'.$value.'</textarea>';
                        $buf .= "</td>\n";
                    } else if ($type == "select") {
                        $items = $field[4];
                        
                        $default = $field[5]=='NoDefault' ; // only values should be displayed without '--Select--' option !
                        $buf.= "<td  $valign class='fieldtitle'>".htmlentities($title)."</td><td  $valign class='fieldvalue'>";
                        if ($readonly == ""){
                            $buf.= Html::select($name,$items,$value,null,"width: 100%;",$default ? null : "-- Select --",$readonly!="");
                        } else {
                            $buf.=$value;
                        }
                        $buf .= "</td>\n";
                    } else if ($type == "multiSelect") {
                        $items = $field[4];
                        $buf.= "<td  $valign class='fieldtitle'>".htmlentities($title)."</td><td  $valign class='fieldvalue'>";
                        if ($readonly == ""){
                            $buf.= Html::multiSelect($name,$items,$value,null,"width: 100%;");
                        } else {
                            $buf.=$value;
                        }
                        $buf .= "</td>\n";
                    } else if ($type == "checkbox") {
                        $buf.= "<td  $valign align='left' class='fieldtitle' colspan='2'>".Html::checkbox($name,$value,$field[4],$field[5])."&nbsp;".htmlentities($title)."</td>\n";
                    } else if ($type == "radio") {
                    	$buf.= "<td  $valign align='left' class='fieldtitle' colspan='2'>".Html::radio($name,$field[4],$value,$field[5])."&nbsp;".htmlentities($title)."</td>\n";
                    } else if ($type == "hidden") {
                        $hidden .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" id="'.$name.'"/>'."\n";
		            } else if ($type == "file") {
                $size = $row[4];
		                if ($title){
		                    $buf .= "<tr><td $valign class='fieldtitle'>".htmlentities($title)."</td>";
		                }
		            	$buf .= "<td colspan=\"$colspan\">";
		                $buf .= '<input style="width:100%;"  type="'.$type.'" name="'.$name.'" size="'.$size.'" id="'.$name.'"/>';
		                $buf .= "</td></tr>\n";
		            }                                        }
                $buf.="</tr>";
            }
            $buf.="</table></td></tr>\n";
        }
        $buf .= '<tr><td align="center" style="padding: 10px;">';
        if ($extrabuttons) {
            foreach ($extrabuttons as $id => $title) {
                $buf.="<input type='button' style='padding-top: 3px;padding-bottom: 3px;width:100px' id='".$id."' value='".$title."'/>&nbsp;";
            }
        }
        if ($action) {
            $buf.= '<input id="submit" style="padding-top: 3px;padding-bottom: 3px;width:100px;" type="submit" value="'.$submitTitle.'"/>';
        }
        $buf.='</td></tr>';
        $buf .= "</table>\n";
        $buf .= $hidden."</form>\n";
        return $buf;
    }

    /**
     * Creates a checkbox input element
     *
     * @param <type> $name
     * @param <type> $value
     * @return <type>
     */
    function checkbox($name,$value,$default_value = '1',$class=null) {
    	$default_value = $default_value === null ? '1' : $default_value;
        $checked = $value == $default_value ? 'checked = "checked"' : "";
        $buf= "<input type=\"checkbox\" name=\"".$name."\" value=\"".$default_value."\" $checked  id=\"".$name."\" class=\"".$class."\">";
        return $buf;
    }
    
    /**
     * Creates a radiobutton input element
     *
     *	@param <type> $id
     * @param <type> $name
     * @param <type> $value
     * @return <type>
     */
    function radio($name,$group,$value,$default_value = '1',$class=null) {
    	$default_value = $default_value === null ? '1' : $default_value;
        $checked = $value == $default_value ? 'checked = "checked"' : "";
        $buf= "<input type=\"radio\" name=\"".$group."\" value=\"".$default_value."\" $checked  id=\"".$name."\" class=\"".$class."\">";
        return $buf;
    }
    
    /**
     * Create just a single select input widget to be used
     * in a custom form.
     *
     * @param <type> $data
     * @param <type> $value
     * @param <type> $class
     */
    function select($name, $items, $value=null, $class=null, $style=null, $allmsg = "-- Select --") {
        $buf ='<select id="'.$name.'"  name="'.$name.'" class="'.$class.'" style="'.$style.'">';
        if ($items) {
            $buf.= $allmsg ? "<option value=''>".$allmsg."</option>" : '';
            foreach ($items as $item) {
                if (is_array($item)) {
                    $selected = $value == $item[1] ? ' selected = "true" ' : "";
                    $buf .= '<option value="'.htmlspecialchars($item[1]).'"'.$selected.'>'.htmlentities($item[0]).'</option>';
                } elseif (is_a($item, "DbObject")) {
                    $selected = $value == $item->id ? ' selected = "true" ' : "";
                    $buf .= '<option value="'.htmlspecialchars($item->getSelectOptionValue()).'"'.$selected.'>'.htmlentities($item->getSelectOptionTitle()).'</option>';
                } elseif (is_scalar($item)) {
                    $selected = $value == $item ? ' selected = "true" ' : "";
                    $buf .= '<option value="'.htmlspecialchars($item).'"'.$selected.'>'.htmlentities($item).'</option>';
                }
            }
        }
        $buf.='</select>';
        return $buf;
    }
    
    /**
     * Create a grouped select input widget to be used
     * in a custom form.
     *
     * @param <type> $name: name of the select box, group name is pre-defined as $name.'_group';
     * @param <type> $items: associative array including group=>groupitems pairs;
     * @param <type> $value: current value of option item;
     * @param <type> $groupvalue: current group value of optgroup item;
     */
    function groupSelect($name, $items, $value=null, $groupvalue=null, $class=null, $style=null, $allmsg = "-- Select --") {
        $buf ='<select id="'.$name.'"  name="'.$name.'" class="'.$class.'" style="'.$style.'">';
        if ($items) {
            $buf.= $allmsg ? "<option value=''>".$allmsg."</option>" : '';
            foreach ($items as $groupname=>$groupitems) {
            	$buf.= '<optgroup label="'.$groupname.'">';
            	foreach ($groupitems as $item) {
	        		if (is_array($item)) {
	                    $selected = ($groupvalue == $groupname && $value == $item[1]) ? ' selected = "true" ' : "";
	                    $buf .= '<option value="'.htmlspecialchars($item[1]).'"'.$selected.'>'.htmlentities($item[0]).'</option>';
	            	} elseif (is_a($item, "DbObject")) {
	                    $selected = ($groupvalue == $groupname && $value == $item->id) ? ' selected = "true" ' : "";
	                    $buf .= '<option value="'.htmlspecialchars($item->getSelectOptionValue()).'"'.$selected.'>'.htmlentities($item->getSelectOptionTitle()).'</option>';
	                } elseif (is_scalar($item)) {
	                    $selected = ($groupvalue == $groupname && $value == $item) ? ' selected = "true" ' : "";
	                    $buf .= '<option value="'.htmlspecialchars($item).'"'.$selected.'>'.htmlentities($item).'</option>';
	                }
            	}
                $buf.= '</optgroup>';
            }
        }
        $buf.='</select><input type="hidden" value="'.$groupvalue.'" name="'.$name.'_group">';
        
        $buf .='<script type="text/javascript">$("#'.$name.' > optgroup").click(function(){$("[name='.$name.'_group]").attr("value", $(this).attr("label"));});</script>';
        
        return $buf;
    }

    /**
     * Create a multi select field using jQuery
     * 
     * @param <type> $name
     * @param <type> $items
     * @param <type> $values
     * @param <type> $class
     * @param <type> $style
     * @param <type> $allmsg
     * @return <type>
     */
    function multiSelect($name,$items,$values=null,$class=null,$style=null, $allmsg = null) {
        $buf ='<select  multiple="multiple" id="'.$name.'"  name="'.$name.'[]" class="'.$class.'" style="'.$style.'">';
        if ($items) {
        	foreach ($items as $item) {
                if (is_array($item)) {
                    $selected = $values && in_array($item[1], $values) ? ' selected = "true" ' : "";
                    $buf .= '<option value="'.htmlspecialchars($item[1]).'"'.$selected.'>'.htmlentities($item[0]).'</option>';
                } elseif (is_a($item, "DbObject")) {
                    $selected = $values && in_multiarray($item->id, $values) ? ' selected = "true" ' : "";
                    $buf .= '<option value="'.htmlspecialchars($item->getSelectOptionValue()).'"'.$selected.'>'.htmlentities($item->getSelectOptionTitle()).'</option>';
                } elseif (is_scalar($item)) {
                    $selected = $values && in_array($item, $values) ? ' selected = "true" ' : "";
                    $buf .= '<option value="'.htmlspecialchars($item).'"'.$selected.'>'.htmlentities($item).'</option>';
                }
            }
        }
        $buf.='</select>';
        $webroot = WEBROOT;
        $buf .=<<<EOT
                <script>
                $('#$name').asmSelect({addItemTarget: 'bottom', removeLabel: '<img src="$webroot/img/bin_closed.png" border="0"/>'});
                $('#$name').change(function(e, data) { $.fn.colorbox.resize(); });
                </script>
EOT;
        return $buf;
    }

    /**
     * Create a single select autocomplete widget
     *
     * @param <type> $data
     * @param <type> $value
     * @param <type> $class
     */
    function autocomplete($name, $options, $value=null, $class=null, $style=null, $minLength=1) {
        $acp_value = $value;
        if (is_array($options)) {
            $source = "[";
            foreach ($options as $option){
                if (is_array($option)) {
                    $source .= '{"id":"'.$option[1].'","value":"'.$option[0].'"}, ';
                    if ($value == $option[1]) {
                        $acp_value = $option[0];
                    }
                } elseif (is_a($option, "DbObject")) {
                    $source .= '{"id":"'.htmlentities($option->getSelectOptionValue()).'","value":"'.htmlentities($option->getSelectOptionTitle()).'"}, ';
                    if ($value == $option->getSelectOptionValue()) {
                        $acp_value = $option->getSelectOptionTitle();
                    }
                } elseif (is_scalar($option)) {
                    $source .= '{"id":"'.$option.'","value":"'.$option.'"}, ';
                } 
            }
            $source .= "]";
        } else {
            $source = "'".$options."'";
        }
        $buf ='<input type="hidden" id="'.$name.'"  name="'.$name.'" value="'.$value.'"/>';
        $buf.='<input type="text" id="acp_'.$name.'"  name="acp_'.$name.'" value="'.$acp_value.'" class="'.$class.'" style="'.$style.'"/>';
        $buf.="<script type='text/javascript'>";
        $buf.='$(function(){
                    $("#acp_'.$name.'").autocomplete({
                        minLength:'.$minLength.', 
                        source: '.$source.',
                        select: function(event,ui){
                            $("#'.$name.'").val(ui.item.id);flow_acp_'.$name.'(event,ui);}
                    });
                });';
        $buf.="</script>";
        return $buf;
    }


    function img($src,$alt="") {
        $buf='<img border="0" src="'.$src.'" alt="'.$alt.'"/>';
        return $buf;
    }


    /**
     * validates the request parameters according to
     * the rules passed in $valarray. It must be of the
     * following form:
     *
     * array(
     *   "<param-name>" => array("<regexp>","<error message>"),
     *   "<param-name>" => array("<regexp>","<error message>"),
     *   ...
     * )
     *
     * returns an array which contains all produced error
     * messages
     */
    function validate($valarray) {
        if (!$valarray || !sizeof($valarray)) return null;
        $error = array();
        foreach ($valarray as $param => $rule) {
            $regex = $rule[0];
            $message = $rule[1];
            $val = $_REQUEST[$param];
            if (!preg_match("/".$regex."/", $val)) {
                $error[]=$message;
            }
        }
        return $error;
    }

}
