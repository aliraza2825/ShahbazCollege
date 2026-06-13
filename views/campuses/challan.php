<html lang="en">
	<head>
    	<title><?php echo $campuses[0]['campus_name'];?></title>
        
        <style>
        	*{
				margin:0;
				padding:0;
			}
			.container{
				width:100%;
			}
			.portion
			{
				
			}
        </style>
    </head>
    <body>
    	<div class="container">
        	<div class="portion">
            	<?php
                	$str=498;
					echo str_pad($str,10,0,STR_PAD_LEFT);
				?>
            </div>
            <div class="portion">
            	<form>
<p><input type="button" value="Print This Page" onClick="window.print()"></p>
</form>
            </div>
        </div>
    </body>
</html>