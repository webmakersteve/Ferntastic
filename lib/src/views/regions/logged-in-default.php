<div class="mc-container">
                  <div class="main-content">
                  	                  					  	    <div class="w-container">
    	<div class="w-col">
        	<div class="w-col-wrapper">
            	
                <div class="widget">
                	                    <div class="p-site-select" style="float: right; padding-top: 4px; display:block;">
                    <form action="/account.php" method="get">
                    	<select id="p-site-select" name="site-select">
                        	<option value="1">Dennis Taylor Trucking</option>
                            <option value="2">Patsy's Restaurant</option>
                            <option value="4">Socrates Society</option>
                        </select>
					</form>
                    </div>
                                    	<div class="title">Data Usage</div>
                    <div class="w-content">
						                        <p>Below is your data usage of 91ferns Servers. The data allowance of your current plan is <strong>2048</strong> Megabytes (MB). You are currently using <strong>1323.37</strong> Megabytes.</p>
                        <p>To help lower your data usage, you can utilize the <span class="ferns">91ferns</span> Cleanup Utility. This will help remove unused files and entries to lower your data usage.</p>                        <div class="progress" style="margin-left: -7px;">
                            <div class="p-wrapper">
                                <div class="p-text" style="color: white;">64.62% Full</div>
                                <div class="p-value" id="data-percent" style="width: 64%;">&nbsp;</div>
                            </div>
                        </div>
                        <script type="text/javascript">
							document.getElementById('data-percent').style.width="0%";
							
							$(function() {
								
								document.getElementById('data-percent').style.width=64+"%";
							/*var t = window.setInterval(function() {
								percent = 64.62;
								elem = document.getElementById('data-percent');
								width = parseInt(elem.style.width);
								
								if (width>=percent) {
									window.clearInterval(t);
									return;	
								}
								
								newWidth=width+1;
								if (newWidth>percent) newWidth=percent;
								elem.style.width=newWidth+"%";
								
								$('.p-text').html(newWidth+"% Full");
								
								
								
							}, 100);*/
							
							function resizeProgress() {
								return;
								w = window.innerWidth;
								ww = w-310;
								
								if (ww<=640) {
									ww=640;
								}
								
								widgetWidth = Math.floor( (ww/3) - 50 );
								$('.progress').css('width', widgetWidth-20+"px");
								$('.p-wrapper').css('width', widgetWidth-30+"px");
								
							}
							
							$(window).resize(function(e) {
								resizeProgress();
							});
							
							resizeProgress();
							
							});
						</script>
                    
                    </div>
                </div>
                
            </div>
        </div>
        <div class="w-col">
        	<div class="w-col-wrapper">
            	
                <div class="widget">
                    <div class="p-site-select" style="float: right; padding-top: 4px; display:none;">
                    <form action="/account.php" method="get">
                    	<select id="p-site-select" name="site-select">
                        	<option value="1">Dennis Taylor Trucking</option>
                            <option value="2">Patsy's Restaurant</option>
                            <option value="4">Socrates Society</option>
                        </select>
					</form>
                    </div>
                	<div class="title">Quick Links</div>
                    <div class="w-content">
                                                            	<ul>
                                                	<li><a href="http://socratessociety.com/wp-login.php">Socrates Society Site Login</a></li>
                                                	<li><a href="http://patsys.com/wp-login.php">Patsy's Login</a></li>
                                                </ul>
                                        </div>
				</div>
			</div>
        </div>
        <div class="w-col"> </div>
        <div style="clear:both;">&nbsp;</div>
  	</div>
                                          </div>
              </div>