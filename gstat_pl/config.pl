#!/usr/bin/perl

our $host_name_web = 'http://xxx.xxx.xxx.xxx/gitstat/';	# domain name web  
our $repository    = '/home/gitstat/gstat_git/.git';	# internal:Kernel repository  directory
our $kfm_rw	   = '/home/gitstat/gstat_rw';      # internal:gitstat r/w area

our $GIT_PATH 	= '/usr/local/bin/';					# git directory 
our $dsn        = 'dbi:mysql:gitstat';					# DB server name
our $user       = 'selp';						# DB user name
our $pass       = 'selp';						# DB password

our $log        = 1;							# if you remain Log file.
our $admin      = 'selp@samsung.com';		        		# Admin e-mail address
our $mail_head  = 'Linux Kernel changes notification e-mail';	       	# Mail Header 
our $mail_tail  = 'Thank you.';			 			# Mail Footer

our $support_rss = 1;
our $max_rss     = 1000;

return 1;
