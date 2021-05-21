				<p class="date"><img src="images/timeicon.gif" alt="" /></p>
			</div>
			<div id="right">
				<div id="searchform">
						<p>Monitor gitstat
  						</p>
				</div>
				<?
				if (loggedin()) {
				?>
				<div id="rightmenu">
					<a href="./join.php">My Account</a>
					<a href="./logout.php?url=index.php">Logout</a>
				</div>
				<?} else {
				?>
				<div id="rightmenu">
					<a href="./join.php">Join</a>
					<a href="./login.php">Login</a>
				</div>
				<?}
				?>
				<div style="background: #EFEFEF;color: #808080;line-height: 1.4em;padding: 8px;word-wrap: break-word;margin: 3px 0 3px 0;">
					<p>Description  :<br><font color=black><?=$projectparams['GIT_TREE_DESC']?></font><br>
						Owner  :<br><font color=black><?=$projectparams['GIT_TREE_OWNER']?></font><br>
					</p>
				</div>					
				
				<div class="rightarticle">
					<p>
						Links<br /><!--FIXME: Should be project specific-->
						<a href="http://www.kernel.org/">Kernel.org</a><br />
						<a href="http://www.celinuxforum.org/">CE Linux Forum</a><br />
						<a href="http://www.kernel.org/pub/scm/linux/kernel/git/torvalds/linux-2.6.git">Linus' Kernel Tree</a><br />
					</p>
				</div>
			
				<div class="rightarticle_ad">
					<a href="http://sourceforge.net/projects/gitstat/">gitstat</a>
				</div>
				<div class="rightarticle_ad">
					<a href="./gstat_rw/rss/gitstat.rss">gitstat RSS</a>
				</div>
			</div>
		
		</div><!--end of div article-->
	</div><!--end of div content-->
			
	<div id="footer">
		<p style="float: left;"><a href="#">Archive</a><img src="images/separator.gif" alt="" /><a href="./gstat_rw/rss/gitstat.rss">RSS Feed</a>
		<br /><a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a> and <a href="http://validator.w3.org/check?uri=referer">XHTML</a><img src="images/separator.gif" alt="" /><a href="#">Accessibility</a></p>
		<p style="float: right; text-align: right;">
		Powered by <a href="http://sourceforge.net/projects/gitstat/">gitstat</a> <br><img src="images/separator.gif" alt="" />Design: <a href="http://www.oswd.org/">OSWD.ORG</a></p>
	</div>
</body>	
</html>
