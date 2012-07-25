
<?php 
			
if( $dateStamp){
	
	//echo " tpl date: ".date('d-m-Y H:i:s', $dateStamp);
	
	// ---------------------------------
	// hrs and events display settings
	//----------------------------------
	// check what is hours limits settings:
	$uid = $w->auth->user()->id;
	$hStartSetArr = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid, 'title'=>'userHrsStart'));
	$hStartSet = $hStartSetArr[0]->value;
	$hEndSetArr = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid, 'title'=>'userHrsEnd'));
	$hEndSet = $hEndSetArr[0]->value;
		  	
	$hstart = $hStartSet ? (int)$hStartSet : 0;
	$hend = $hEndSet ? (int)$hEndSet : 24;
			
	$hnum = $hend - $hstart; // 24 hrs slots 1px border for each;
	//aDebug('$hstart='.$hstart.' $hend='.$hend.' $hnum='.$hnum);
			
	// the width of one hr depends on visible hrs - the user settings:
	$width_px = floor(1080/($hnum)); 
	// $hnum == num of 1px borders width for hrs divs-grid; width = 45 for 0-24
	$hrsGridWidth_px = floor((1080-$hnum)/$hnum); // it is too long ? 
	$minWidth_px = ($width_px+1)/60; 
	//aDebug('$minWidth_px '.$minWidth_px);
			
	$dayCut = date('Y-m-d',$dateStamp);
			
	$dd = date('d',$dateStamp);
	$mm = date('m',$dateStamp);
	$yy = date('Y',$dateStamp);
	$zeroTime = strtotime('+'.$hstart.' hours', mktime(0,0,0,$mm,$dd,$yy));
	//$zeroTime = strtotime('+'.$hstart.' hours', $dayCut); //'+'.$hstart.' hours'
			
			
		
	// Whole Day Div:
    print "<div class='agDay' id='".date('Y-m-d',$dateStamp)."' style='overflow:hidden; border: 1px solid grey; width:1500px; position:relative;'>";
	
	    //Inner 1: Date ~= 19-May Div:
	    // width: 419px
		print "<div style='
							background: #9fc; 
							border-bottom: 1px solid grey;
							border-right: 1px solid grey;
							width:418px; 
							height: 35px; 
							float:left; 
							overflow:hidden;'>".
				date('d-M',$dateStamp).
				"<button class='addEvent' id='{$dateStamp}' style='float:right;'>+add</button>".
				"</div>"; // End Inner 1 Div.
		
		//Inner 2: Events time in one row:
		// width: 1081px; but 1080 - available.
		print "<div id='".date('Y-m-d',$dateStamp)."' 
						style='background: #ebebeb; 
						border-bottom: 1px solid grey;
						border-right: 1px solid grey; 
						width: 1080px; 
						height: 35px; 
						float:left;
						position:relative;
						overflow:hidden;'>";
		
		
			// display hrs 'grid':
			for($h=$hstart; $h<$hend; $h++){
				//echo('start: '.$start_px."  width: ".$width_px); left:".$start_px."px; 
				print "<div class='hrs' 
						style='
						border-left: 1px solid grey;  
						float: left;
						width:".$hrsGridWidth_px."px; 
						height: 33px; 
						margin:0;'>$h
						</div>";
			}
			
			
			$zindex = 1;
			if($events)
			{
				foreach ($events as $ev)
				{
					 $zindex++;
					// sec to Pos-min-start 
					$pos = ($ev['dt_start'] - $zeroTime)/60;    
					
					// minute width(for 0-24hrs display): (1080)/24=45px per hr +1px.border=46 /60 = 0.7667px per min
					$start_px = ceil( $pos*$minWidth_px); // 46/60=0.7666666666 
					
					// supposed to be positive :
					// check time and set width
					if($ev['dt_start'] < $ev['dt_end']){
						// sec to min: $ev['dt_end'] - $ev['dt_start'] )/60   
						$width_px = ceil( (( $ev['dt_end'] - $ev['dt_start'] )/60)*$minWidth_px ); //($width_px+1)/60
					}else{
						// incorrect settings!
						$width_px = 20;
					}
					
					// Dates can be set to different days by mistake. No checking for now.
					// longer than 1 day events ?
					if($width_px > 1100) $width_px = 100;
					 
					print "<div class='dt_start' 
							style='
								z-index:".$zindex."; 
								border: 1px solid grey; 
								background: #9fc;
								position: absolute;
								left:".$start_px."px;
								top: 18px; 
								width:".$width_px."px; 
								height: 15px; 
								margin:0;'
								>
							</div>";
					
				}	
			}
		
		
		print"</div>"; // End Inner 2 Div.
	// end top row
	
		
		
	print "<br  style='clear:both;'>";
	
	
	// -------------------------------------------
	//  All Events
	//-------------------------------------------
	if($events)
	{			
		foreach ($events as $ev)
		{
			//-------------------------------------------
			//  TIME && TITLE
			//-------------------------------------------
			$spanEvTitle = $ev['title'];
			$body = date('h:i a', $ev['dt_start'])." - ".date('h:i a', $ev['dt_end'])."<br>";
			$body .= $ev['type']."<br>";
			$body .= $ev['busy'] ? 'busy time' : 'available time';
			
			// Inner Date Div :
			print "<div style='
							border-bottom: 1px solid grey;
							border-right: 1px solid grey;
							width:418px; 
							height: 35px; 
							float:left;
							overflow:hidden;
							'>";
			
				print "<span class='dt_start' >";
					
					// check User access base on ownership or AgUsersInGroups->role property:
							$userAllowed = true;	
							if($userAllowed){
								$evObj = $w->Agenda->getAgs('AgEvent', $ev['id']);
								$evInfo = $evObj->getInfoStr();
								//style='float:right;'
								print "<button class='editEvent' id='{$dateStamp}' name='{$evInfo}' style='height: 21px;background: url({$webroot}/img/edit.png) no-repeat;'></button>&nbsp";
							}
							
						print date('h:ia', $ev['dt_start'])."-".date('h:ia', $ev['dt_end']).'. ' ;
						
				print "</span>";
				
				/*style='
												
												width:220px; 
												height: 35px; 
												float:left; 
												margin:0; 
												'border: 1px solid green; 
												*/
				print "<span class='title' title=\"cssbody=[eventBody] cssheader=[eventHeader] header=[$spanEvTitle] body=[$body] fade=[off]\"
												>";
					
					$evTitle = strlen($ev['title']) > 35 ? substr($ev['title'], 0,35) : $ev['title']; 
					//$evTitle = $ev['title'];
					print $evTitle;
				
			    
			print"</span>";
			print"</div>"; 
			
			
			// check if event is out of display hrs range - continue without displaying event body;;
			
			//-------------------------------------------
			//  EVENT Color Boby
			//-------------------------------------------
			//Inner one Event time Div-Long Row: important to set - position:relative;
			print "<div id='".date('Y-m-d',$events[0]['dt_start'])."' 
						style='background: #ebebeb; 
						border-bottom: 1px solid grey; 
						width: 1080px; 
						height: 35px; 
						float:left;
						position:relative;
						'>";
			
				$pos = ($ev['dt_start'] - $zeroTime)/60; // min
				//$start_px = ceil( $pos*0.7667);
				// minute width(for 0-24hrs display): (1080)/24=45px per hr +1px.border=46 /60 = 0.7667px per min
				$start_px = floor( $pos*$minWidth_px); // 46/60=0.7666666666 //ceil 
				
				// check time and set width
				if($ev['dt_start'] < $ev['dt_end']){
					$width_px = ceil( (( $ev['dt_end'] - $ev['dt_start'] )/60)*$minWidth_px );
					//$width_px = ceil( (( $ev['dt_end'] - $ev['dt_start'] )/60)*($width_px+1)/60 );
					
				}else{
					$width_px = 20;
				}
				
				// more than 1 day events ?
				if($width_px > 1100) $width_px = 100;
				
				//echo('start: '.$start_px."  width: ".$width_px); //position: absolute;  background: #9fc; 
				//$info .=  "<span title=\"cssbody=[eventBody] cssheader=[eventHeader] header=[$e[title] $confStr] body=[$body] fade=[off]\">".
				//			$conf.$start." <a href=\"$url\">".$e[title].$b2."</a></span>".$confirmButton; 
				//<span title=\"cssbody=[eventBody] cssheader=[eventHeader] header=[$spanEvTitle] body=[$body] fade=[off]\"></span>
				 
				// one event short Div:
				print "<div class='dt_start' 
						style='
							 
							border: 1px solid grey; 
							background: #9fc;
							position: absolute;
							left:".$start_px."px;
							top: 15px; 
							width:".$width_px."px; 
							height: 17px; 
							margin:0;'
							title=\"cssbody=[eventBody] cssheader=[eventHeader] header=[$spanEvTitle] body=[$body] fade=[off]\"
							></div>";
				
			print"</div>"; 
			
			//end event row
			print "<br  style='clear:both;'>";
		}
	}				

	
	print "</div>"; // end Whole Day Div
	
	
				
}
			

	//aDebug($events);
	print $noScheds;
	
	?>
	
