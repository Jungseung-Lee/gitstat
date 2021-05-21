#!/usr/bin/perl

#############################################################
#															#
#	Gitstat's Perl part (v0.11)								#
#															#
#	Usage : ./gitstat.pl HEAD -> init DB					#
#			./gitstat.pl      -> update git and DB			#
#			./gitstat.pl HEAD NOMAIL 						#
#			./gitstat.pl NOMAIL		 						#
#							  -> without sending mail	    #
#	Last Modified : 2008. 2. 11								#
#															#
#############################################################


use strict;
use CGI qw(:standard :escapeHTML -nosticky);

use Encode;
use DBI;

require "config.pl";
require "lib/log.pl";
require "lib/lib.pl";
require "lib/parser.pl";
require "lib/mailing.pl";
require "lib/getuser.pl";
require "lib/generaterss.pl";
require "lib/Lite.pm";

our $dsn;					# DB server name
our $user;					# DB user ID
our $pass;					# DB Password
our $log;					# Log support?
our $repository;				# GIT repository
our $support_rss;				# RSS support?
our $current_epoch = time();			# for Mail count
our $nomail_option = 0;
sub main {

	my $hash;
	my $update;

	if (@ARGV[0] eq "HEAD") {			# HEAD or recent?
		print "----------------------------------------------------------\n";
		print " Processing... It can take a long time (around 40 minutes)\n";
		$hash = @ARGV[0];
		$update = 0;                  
	} elsif (@ARGV[0] eq "NOMAIL" || !defined @ARGV[0]) {
		print "----------------------------------------------------------\n";
		print " Processing... It can take some time (around 3 minutes)\n";
		$hash = &recent_commit().'..';
		$update = 1;
	} else {
		print "\nGitStat Ver 0.11\n\n";
		print "\tUsage : perl ./gitstat.pl [HEAD] [NOMAIL]\n\n";
		exit();
	}

	
	if (@ARGV[0] eq "NOMAIL" || @ARGV[1] eq "NOMAIL") {			# NOMAIL?
		$nomail_option = 1;
		print " Mail option Deactivated                    \n";
		print "----------------------------------------------------------\n";
	}else
	{
		print " Mail option Activated                  \n";
		print "----------------------------------------------------------\n";
	}
	my $dbh = DBI->connect($dsn, $user, $pass) or &klog($DBI::errstr, &log, 0);	# DB handle
	my $sql;			# SQL statement
	my $sth;			# DB Statement Handle
	my $fh;				# file contorl handle
	my $line;			# file read line
	my @tagslist;			# tag list array

	my $index = 0;
	my $uindex = 0;
	my $cindex = 0;

	my @user;			# user information
	my $ref;			# user reference variable
	my @tmp;
	my $tmp;

# Here parsed result
	my @subject;		# has mail contents as row.
	my @commit;			# Link address for Mail & RSS
	my @content;		# commit content
	my @category;		# category array
	my @author;			# author array
	my @epoch;			# epoch array
	my @paths;			# array
	my @categorized_msg_idx;
	my @root_msg_idx;
	my @merge_msg_idx;
	my @category_str;
# End
	my $version;			# version no.

	@user = &getUserInfo($dbh);

	$repository =~ m/^(.*)\.git/;
	chdir($1);				# goto git repository...

if ($update == 1) {
	open $fh, "-|", 'git-fetch'
		or &klog("Update error.", $log);
	while (<$fh>) { }
	close $fh
		or &klog("Update error.", $log);
	open $fh, "-|", 'git-fetch', '--tag'
		or &klog("Update error.", $log);
	while (<$fh>) { }
	close $fh
		or &klog("Update error.", $log);
	open $fh, "-|", 'git-pull'
		or &klog("Update error.", $log);	
	while (<$fh>) { }
	close $fh
		or &klog("Update error.", $log);
}										# Git repository update

	@tagslist = &get_tags_list();		# Get tag list.
	$version = ($#tagslist + 1) + 1;	# Calculate index of recent version.

	#if there is new tag..
	while (@tagslist) {
		my $tag_id = pop @tagslist;
		my %tag = &parse_tag($tag_id);	# it parses tag information.
		my $comment = $tag{'comment'};	# tag comment
		$comment = join "", @$comment;

		if (&exist_db($dbh, "v_tag", "id = '$tag{'id'}'")) {
			next;
		} else {
			$sql = "INSERT INTO v_tag (id, object, type, name, author, epoch, tz, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			$sth = $dbh->prepare($sql)
				or &klog($dbh->errstr, $log, 0);
			$sth->execute($tag{'id'}, $tag{'object'}, $tag{'type'}, $tag{'name'}, $tag{'author'}, $tag{'epoch'}, $tag{'tz'}, $comment);
		}
	}										# Append Tag informail into DB
	
	open $fh, "-|", &git_cmd(), "rev-list", "--abbrev-commit", $hash		# Generate commit list to be parsed.
		or &klog("git-rev-list --abbrev-commit error", $log, 0);			# e.g. git-rev-list --abbrev-commit (HEAD)

	while ($line = <$fh>) {
		my %co;					# commit hash array
		my $parent;				# a parent
		my $parents;			# list of parents
		my $no_parent;			# the number of parent
		my @difftree;

		my $content;			# for commit comment join
		my $category = '';		# It has commit categories which are tokenized '/';
		my $paths = '';			# diff-tree pathspec
		my $tmp2;				# temporary

		my @row;				# DB control variable. fetchrow_array function's return.
		my $result;				# DB control variable. SELECT query's selected tuple #.

		chomp $line;			# remove new line

		$result = &getVersion($dbh, $line);
		if (defined $result) {
			if ($result == -1) {
				for ($index = 0; $index <= $#commit; $index = $index + 1) {
					if (!&remove($commit[$index])) {
						&klog("DB recovery failed.", $log, 0);
					}
				}
				&klog("Fatal error occurs.", $log, 0);
			} else {
				$version = $result;
			}
		}

		$version = 0 if (!defined $version);

		%co = &parse_commit($line);			# Parse commit
		if (!(defined %co)) { &klog("Commit parsing failed.", $log, 0); }

		$parent = $co{'parent'};
		$parents = $co{'parents'};
		$no_parent = @$parents;				# get # of parent

		$parents = join ' ', @$parents;		# generates parent list

			if (!defined $parent) {		# if no parent, it has root as parent
				$parent = "--root";
				$parents = "--root";
			}

		$content = $co{'comment'};
		$content = join "\n", @$content;	# converts comment array to string.

		if ($no_parent <= 1) {				# categorizes commit which is not merged.
			my $fh2;
			my $line2;

			open $fh2, "-|", &git_cmd(), "diff-tree", '-r', "--no-commit-id",
				 '-M', $parent, $line 
					 or &klog("git-diff-tree open error", $log, 0);
			@difftree = map {chomp; $_} <$fh2>;
			close $fh2 
				or &klog("git-diff-tree close error", $log, 0);

			foreach $line2 (@difftree) {
				my %diff = &parse_difftree_raw_line($line2);	# parsing diff-tree information
				my $path;										# diff pathspec

				my @subcategory = (0, 0, 0, 0);					# category no
				my @str_category;								# category name

				if ($diff{'status'} eq 'R' || $diff{'status'} eq 'C') {
					$path = $diff{'to_file'};
				} else {
					$path = $diff{'file'};
				}

				$paths = $paths.$path.";";				# tokenizer is semicolone.
				@str_category = split ('/', $path);		# a/b/c -> (a, b, c)
				for ($index = 1; $index < 5; $index = $index + 1) {
					if (!defined $str_category[$index]) { last; }	# categorizes only directories.
					$sql = "SELECT no FROM category$index WHERE subcategory$index = ?";
					$sth = $dbh->prepare($sql)
						or &klog($dbh->errstr, $log, 0);
					$result = $sth->execute($str_category[$index-1])
						or &klog($dbh->errstr, $log, 0);
					if ($result == 0) {					# If current category doesn't exist in DB,
						$sql = "INSERT INTO category$index (subcategory$index) VALUES (?)";
						$sth = $dbh->prepare($sql)
							or &klog($dbh->errstr, $log, 0);
						$sth->execute(@str_category[$index-1])
							or &klog($dbh->errstr, $log, 0);

						$sql = "SELECT no FROM category$index WHERE subcategory$index = ?";
						$sth = $dbh->prepare($sql)
							or &klog($dbh->errstr, $log, 0);
						$result = $sth->execute($str_category[$index-1])
							or &klog($dbh->errstr, $log, 0);
					}
					@row = $sth->fetchrow_array();
					$sth->finish();
					$subcategory[$index-1] = $row[0];
				}
				if ($index > 1) {		# If categorized.
					$category_str[$subcategory[0]] = $str_category[0] if (!defined $category_str[$subcategory[0]]);
					if (!($category =~ m!^$subcategory[0]/!) & !($category =~ m!/$subcategory[0]/!)) {
						$category = $category.$subcategory[0].'/';	# accumulates only the First category
					}
					if (!($categorized_msg_idx[$subcategory[0]] =~ m!^$cindex/!) & !($categorized_msg_idx[$subcategory[0]] =~ m!/$cindex/!)) {
						$categorized_msg_idx[$subcategory[0]] = $categorized_msg_idx[$subcategory[0]].$cindex.'/';
					}
					if (!&exist_db($dbh, "Logcategory", "commit = \"$line\" and subcategory1 = @subcategory[0] and subcategory2 = @subcategory[1] and subcategory3 = @subcategory[2] and subcategory4 = @subcategory[3]")) {
						$sql = "INSERT INTO Logcategory (commit, subcategory1, subcategory2, subcategory3, subcategory4) VALUES (?, ?, ?, ?, ?)";
						$sth = $dbh->prepare($sql)
							or &klog($dbh->errstr, $log, 0);
						$sth->execute($line, @subcategory[0], @subcategory[1], @subcategory[2], @subcategory[3])
							or &klog($dbh->errstr, $log, 0);
					}
				}
					# Here, One diff row is parsed.
			}		# End of for each
			if ($category eq "") {
					# It's root tree..
				push @root_msg_idx, $cindex;	
			}
		}		# End of If
		else {		# Merge commit
			push @merge_msg_idx, $cindex;
		}
		if (&exist_db($dbh, 'ChangeLog', "commit = \"$line\"")) { 
			$category = "";
			next; 
		}

		$sql = "INSERT INTO ChangeLog (commit, subject, content, committer, author, commitdate, authordate, parents, tree, path, version) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$sth = $dbh->prepare($sql)
			or &klog($dbh->errstr, $log, 0);
		$sth->execute($line, $co{'title'}, $content, $co{'committer'}, $co{'author'}, $co{'committer_epoch'}, $co{'author_epoch'}, $parents, $co{'tree'}, $paths, $version)
			or &klog($dbh->errstr, $log, 0);

		push @subject, $co{'title'};	# to share parsed information others (RSS & Mailing)
		push @commit, $line;			# accumulates commit no (SHA1 hash value)
		push @content, $content;		# to Insert content into RSS
		push @author, $co{'author'};
		push @epoch, $co{'author_epoch'};

		push @paths, $paths;			# real path
		push @category, $category;		# accumulates category string (which is tokenized by '/') into array

		$cindex = $cindex + 1;			# commit count
		$category = "";					# Clear category string

			# End of one commit
	}

	close $fh;

	if ($cindex > 0) {					# This part is only executed when parsed information exists.
		&klog($cindex.'\'s change(s) occur(s).', $log, 1);
		#&mail_initialize($dbh);		# Initializes environment for mailing.
		foreach $ref (@user) {			# User Array
			my $msg;
			my $msg_txt;
			my $result;
			my $result2;
			my $filename;
			my $th;					#1st,2nd,3rd?
			my $tempname;
			
			$sql = "SELECT subcategory1 FROM Memcategory WHERE user_no = ?"; 
			$sth = $dbh->prepare($sql)
				or &klog($dbh->errstr, $log, 0);
			$result = $sth->execute($$ref{'user_no'})
				or &klog($dbh->errstr, $log, 0);		# Get user which is serviced mailing.
			
			
			if ($result > 0) {
				for ( ; $result >= 0; $result = $result - 1) {
					my @row = $sth->fetchrow_array(); 	#e.g. 30=>sound,19=>kernel
					
					if (defined $categorized_msg_idx[$row[0]]) {
						@tmp = split '/', $categorized_msg_idx[$row[0]];
						$msg = $msg.'<BR><DIV align=center><TABLE border=0 cellpadding=1 cellspacing=1 width=800 bgcolor=#808080><TBODY><TR bgcolor=#ffffff><TD colspan=4><BR><LI><U><B> '.$category_str[$row[0]].'</B></U></LI><BR></TD></TR><TR bgcolor=#f0f0f0><TD width=20 align=center><b>No</b></TD><TD width=380 align=center><b>Subject</b></TD><TD width=150 align=center><b>Author</b></TD><TD width=150 align=center><b>Date</b></TD></TR>'."\n";
						$msg_txt = $msg_txt.$category_str[$row[0]]."\n\n";
						
						$index = 0;
						foreach (@tmp) {
						
							if ($subject[$_] eq "")
							{
								next;
							}
							$index   = $index + 1;
							$msg 	 = $msg.&makeMsg($dbh, $index, $subject[$_], $commit[$_], $author[$_], $epoch[$_], $category_str[$row[0]], $row[0], $paths[$_]);
							$msg_txt = $msg_txt.&makeMsgTxt($dbh, $index, $subject[$_], $commit[$_], $author[$_], $epoch[$_], $category_str[$row[0]], $row[0], $paths[$_]);
						}
						$msg = $msg.'</TBODY></TABLE></DIV>'."\n";
						$msg_txt = $msg_txt."\n\n";
					}
				}
			}
			elsif ($result < 0) { 
				&klog("For ".$$ref{'name'}.', query of Memcategory table is failed.', $log, 1);
				next;
			}		
					
			
		
			$sql = "SELECT filename FROM Memcategory WHERE user_no = ?"; 
			$sth = $dbh->prepare($sql)
				or &klog($dbh->errstr, $log, 0);
			$result2 = $sth->execute($$ref{'user_no'})
				or &klog($dbh->errstr, $log, 0);		# Get user which is serviced mailing.
			
			
			if ($result2 > 0) {
				for ( ; $result2 >= 0; $result2 = $result2 - 1) {	
					
					my @row = $sth->fetchrow_array();
					
					foreach( @row ) #e.g. filename : /sound, /kernel/sched.c # field information
					{
						if ($_ ne '0'){
							$filename = $_;					#Memcategory Information
							$filename =~ s/\///;			#e.g. /kernel/sched.c => kernel/sched.c
							
							$th = 0;
							$index = 0;
							
							foreach (@paths) {				#ChangeLog Information
							
								if ($_ =~ /^$filename/)
								{
								
									if( $filename ne $tempname) 
									{	
										$msg = $msg.'</TBODY></TABLE></DIV>'."\n";
										$msg_txt = $msg_txt."\n\n";
										$msg = $msg.'<BR><DIV align=center><TABLE border=0 cellpadding=1 cellspacing=1 width=800 bgcolor=#808080><TBODY><TR bgcolor=#ffffff><TD colspan=4><BR><LI><U><B> '.$filename.'</B></U></LI><BR></TD></TR><TR bgcolor=#f0f0f0><TD width=20 align=center><b>No</b></TD><TD width=380 align=center><b>Subject</b></TD><TD width=150 align=center><b>Author</b></TD><TD width=150 align=center><b>Date</b></TD></TR>'."\n";
										$msg_txt = $msg_txt.$filename."\n\n";
									}
									
									$index   = $index + 1;
									$msg     = $msg.&makeMsg		($dbh, $index, $subject[$th], $commit[$th], $author[$th], $epoch[$th], $filename, $row[0], $paths[$th]);
									$msg_txt = $msg_txt.&makeMsgTxt ($dbh, $index, $subject[$th], $commit[$th], $author[$th], $epoch[$th], $filename, $row[0], $paths[$th]);
									
								
									$tempname = $filename;
								}
								
								$th++;
							}
						}
							
					}
					
				}
			}
			elsif ($result2 < 0) { 
				&klog("For ".$$ref{'name'}.', query of Memcategory table is failed.', $log, 1);
				next;
			}
			
			
			if ($result eq 0 ) { next;} #if user do not select any category.  && $result2 eq 0
			
			
			if (defined $root_msg_idx[0]) {
				$msg = $msg.'</TBODY></TABLE></DIV><BR><BR><DIV align=center><TABLE border=0 cellspacing=1 cellpadding=1 width=800 bgcolor=#808080><TBODY><TR bgcolor=#ffffff><TD colspan=4><BR><LI><U><B> Kernel Tree Root commit</B></U></LI><BR></TD></TR><TR bgcolor=#f0f0f0><TD width=20 align=center><b>No</b></TD><TD width=380 align=center><b>Subject</b></TD><TD width=150 align=center><b>Author</b></TD><TD width=150 align=center><b>Date</b></TD></TR>'."\n";
				$msg_txt = $msg_txt."Kernel Tree Root commit\n\n";
					
				$index = 0;
				foreach (@root_msg_idx) {
					
					if ($subject[$_] eq "")
					{
						next;
					}
					
					$index = $index + 1;
					$msg = $msg.&makeMsg($dbh, $index, $subject[$_], $commit[$_], $author[$_], $epoch[$_], '', '', $paths[$_]);
					$msg_txt = $msg_txt.&makeMsgTxt($dbh, $index, $subject[$_], $commit[$_], $author[$_], $epoch[$_], '', '', $paths[$_]);
				}
				$msg = $msg.'</TBODY></TABLE></DIV>';
				$msg_txt = $msg_txt."\n\n";
			}
			if (defined $merge_msg_idx[0]) {
				$msg = $msg.'<BR><BR><DIV align=center><TABLE border=0 cellspacing=1 cellpadding=1 width=800 bgcolor=#808080><TBODY><TR bgcolor=#ffffff><TD colspan=4><BR><LI><U><B> Merge Commit</B></U></LI><BR><TR bgcolor=#f0f0f0><TD width=20 align=center><b>No</b></TD><TD width=380 align=center><b>Subject</b></TD><TD width=150 align=center><b>Author</b></TD><TD width=150 align=center><b>Date</b></TD></TR>'."\n";
				$msg_txt = $msg_txt."Merge Commit\n\n";
				
				$index = 0;
				foreach (@merge_msg_idx) {
				
					if ($subject[$_] eq "")
					{
						next;
					}
					
					$index = $index + 1;
					$msg = $msg.&makeMsg($dbh, $index, $subject[$_], $commit[$_], $author[$_], $epoch[$_], '', '', $paths[$_]);
					$msg_txt = $msg_txt.&makeMsgTxt($dbh, $index, $subject[$_], $commit[$_], $author[$_], $epoch[$_], '', '', $paths[$_]);
				}
				$msg = $msg.'</TBODY></TABLE></DIV>'."\n";
				$msg_txt = $msg_txt."\n\n";
			}
			if (defined $$ref{'email'}) {
				
				if(!($msg eq "")){ # if there is new message
					
					if($nomail_option eq "0"){ #should be modify this location
						if ($$ref{'mailhtml'} eq "1")
						{
							&send_mail($$ref{'email'}, $msg, $$ref{'group_id'});
						}else
						{
							&send_mail_txt($$ref{'email'}, $msg_txt, $$ref{'group_id'});
						}
					print "mail send--------------[".$$ref{'email'}."]\n";
					}
				}
			
			}
			else {
				&klog('Send_mail : User not found', $log, 1);
			}
		}
		#&update_mailing_count($dbh);	# Update mail count...
		&genRSS(\@subject, \@content, \@commit, \@author, \@epoch, \@category);
	}					# Update or generate RSS...			
	else{
		print "There are no data for up-to date.\n";
	}
	$sth->finish() if (defined $sth);
	$dbh->disconnect();
	return 1;				# Complete...
}

main();
