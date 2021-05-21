#!/usr/bin/perl

# Mailing Service
#
#
# 2007. 6. 20.

our $host_name_web;
our $current_epoch;		# Extern variables

our $subject_category;
our @subject_no_category;
our @update_count;
our $no_subcategory;		# Accumulated array and associated variables

#sub mail_initialize {
#	my $dbh = shift;
#	my $max_val;
#	my @fetch_row;
#
#	my $sql = "SELECT no FROM category1";
#	my $sth = $dbh->prepare($sql);
#
#	my @array;
#	$no_subcategory = $sth->execute();
#	if ($no_subcategory > 0) {
#		while ((@fetch_row = $sth->fetchrow_array())) {
#			push @array, @fetch_row[0];
#		}
#		$no_subcategory = &get_max(\@array);
#	}
#	$sth->finish();
#
#	for ($cindex = 0; $cindex < $no_subcategory; $cindex = $cindex + 1) {		# Initialization
#		@subject_no_category[$cindex] = 0;
#	}
#}

sub makeMsg {
	my $dbh = shift;
	my $index = shift;		#no
	my $subject = shift;
	my $commit = shift;	#author
	my $author = shift;
	my $epoch = shift;
	my $category = shift;
	my $category_no = shift;
	my $path = shift;
	my $cindex = 0;
	my $msg = '';
	my %date = &get_date($epoch);	#date

	my $sql;
	my $sth;
	my $result;
	my @str_category;
	my @path;

	if ($category ne "") {
		@path = split ';', $path;

		if ($subject_category eq '') {							# Initial subject
			$subject_category = $category;
		#	$subject_no_category[$category_no - 1] = 1;
		} elsif (!($subject_category =~ m/$category/)) {		# Append category to subject
			$subject_category = "$subject_category, $category";
		#	$subject_no_category[$category_no - 1] = 1;
		}

		$msg = "<TR bgColor=#ffffff><TD align=center rowspan=2>$index</TD><TD><b><A href=\"".$host_name_web."/commit-detail.php?commit=$commit\"target=_blank>$subject</A></b></TD><TD align=center>$author</TD><TD align=center>$date{'rfc2822'}</TD></TR>\n";
		$msg = $msg."<TR bgcolor=#ffffff><TD colspan=3>";
		foreach (@path) {
			$msg = $msg."\n&nbsp;&nbsp;&nbsp;&nbsp;".$_."<BR>\n";
		}
		$msg = $msg."</TD></TR>\n";
	} else {
		$msg = "<TR bgColor=#ffffff><TD align=center>$index</TD><TD><b><A href=\"".$host_name_web."/commit-detail.php?commit=$commit\" target=_blank>$subject</A></b></TD><TD align=center>$author</TD><TD align=center>$date{'rfc2822'}</TD></TR>\n";
	}

	return $msg;
}

sub makeMsgTxt {
	my $dbh = shift;
	my $index = shift;
	my $subject = shift;
	my $commit = shift;
	my $author = shift;
	my $epoch = shift;
	my $category = shift;
	my $category_no = shift;
	my $path = shift;
	my $cindex = 0;
	my $msg = '';
	my %date = &get_date($epoch);

	my $sql;
	my $sth;
	my $result;
	my @str_category;
	my @path;

	if ($category ne "") {
		@path = split ';', $path;

		if ($subject_category eq '') {					# Initial subject
			$subject_category = $category;
		#	$subject_no_category[$category_no - 1] = 1;
		} elsif (!($subject_category =~ m/$category/)) {		# Append category to subject
			$subject_category = "$subject_category, $category";
		#	$subject_no_category[$category_no - 1] = 1;
		}

		$msg = " ".$index."    ".$subject."       ".$author."  \n";
		$msg =$msg."       ".$host_name_web."/commit-detail.php?commit=$commit   ".$date{'rfc2822'}."\n";
		
		foreach (@path) {
			$msg = $msg."           ".$_."\n";
		}
		$msg = $msg."\n";
	} else {
		$msg = "  ".$index."    ".$subject."       ".$author."  \n";
		$msg =$msg."       ".$host_name_web."/commit-detail.php?commit=$commit   ".$date{'rfc2822'}."\n";
		$msg = $msg."\n";
	}

	return $msg;
}

sub send_mail {
	my $receiver = shift;
	my $content = shift;
	my $group_id = shift || 0;
	my $index = 0;
	my $type = "text/html";
	my $MIMEver = "1.0";
	my @tmp;
	my $subject = "[Gitstat] Kernel tree $subject_category was changed.";
	my $msg = <<endof_style;
<STYLE type=text/css>A:link {COLOR: darkblue; TEXT-DECORATION: none }\n
	\tA:visited {COLOR: darkblue; TEXT-DECORATION: none}\n
	\tA:active {COLOR: red; TEXT-DECORATION: underline}\n
	\tA:hover {COLOR: darkblue; TEXT-DECORATION: underline}\n
	\tBODY {FONT-SIZE: 9pt; COLOR: #555555; FONT-FAMILY: Arial,Verdana}\n
	\tLI {FONT-SIZE: 9pt; COLOR: #ff3333; FONT-FAMILY : Arial,Verdana}\n
	\tTABLE {FONT-SIZE: 9pt; COLOR: #555555; FONT-FAMILY: Arial,Verdana}\n 
	\tTD {FONT-SIZE: 9pt; COLOR: #555555; FONT-FAMILY: Arial,Verdana}\n
	\tSELECT {FONT-SIZE: 9pt; COLOR: #555555; FONT-FAMILY: Arial,Verdana}\n
	\tDIV {FONT-SIZE: 9pt; COLOR: #555555; FONT-FAMILY: Arial,Verdana}\n
</STYLE>\n
endof_style

	$msg = $msg."<CENTER><TABLE WIDTH=800 CELLSPACING=1 CELLPADDING=1 ALIGN=CENTER BGCOLOR=#808080 BORDER=0><TBODY><TR BGCOLOR=#9999FF><TD><BR>&nbsp;&nbsp;\n";
	$msg = $msg.'<B>'.$mail_head."</B><BR><BR></TD></TR><TR BGCOLOR=#FFFFFF><TD ALIGN=CENTER>\n";
	$msg = $msg.$content."<BR><BR></TD></TR><TR BGCOLOR=#FFFFFF><TD ALIGN=RIGHT>\n";
	$msg = $msg."<A HREF=".$host_name_web." TARGET=_blank><BR><B>&nbsp;&nbsp;&nbsp;&nbsp;<U> > Go to Gitstat ! </U></B></A><BR><BR>\n";
	$msg = $msg.$mail_tail."<BR></TD></TR></TBODY></TABLE></CENTER>";	

    	$msg = new MIME::Lite
                    From 	=> $admin,
                    To 		=> $receiver,
                    Subject 	=> $subject,
                    Type 	=> $type,
					Encoding    => 'quoted-printable',
                    Data 	=> $msg
	;
	if (!$msg->send()) {
		&klog("$receiver : Mail send error...", $log, 1);
	}

	$subject_category = "";

#	if (defined @update_count[$group_id]) {
#		@tmp = split "/", @update_count[$group_id];
#	}
	
#	for ($index = 0; $index < $no_subcategory; $index = $index + 1) {
#		if (defined @tmp[$index]) {
#			@tmp[$index] = @tmp[$index] + @subject_no_category[$index];
#		} else {
#			@tmp[$index] = @subject_no_category[$index];
#		}
#		@subject_no_category[$index] = 0;
#	}
#	$update_count[$group_id] = join "/", @tmp;	
	
	return 1;
}

sub send_mail_txt {
	my $receiver = shift;
	my $content = shift;
	my $group_id = shift || 0;
	my $index = 0;
	my $type = "text/Plain";
	my $MIMEver = "1.0";
	my @tmp;
	my $subject = "[Gitstat] Kernel tree $subject_category was changed.";
	my $msg = "";

	$msg = $msg." ".$mail_head."\n\n\n\n";
	$msg = $msg.$content."\n";
	$msg = $msg."                                                      > Go to Gitstat !(".$host_name_web.") \n";
	$msg = $msg.$mail_tail."\n";	

    	$msg = new MIME::Lite
                    From 	=> $admin,
                    To 		=> $receiver,
                    Subject 	=> $subject,
                    Type 	=> $type,
					Encoding    => 'quoted-printable',
                    Data 	=> $msg
	;
	if (!$msg->send()) {
		&klog("$receiver : Mail send error...", $log, 1);
	}

	$subject_category = "";

#	if (defined @update_count[$group_id]) {
#		@tmp = split "/", @update_count[$group_id];
#	}
#	
#	for ($index = 0; $index < $no_subcategory; $index = $index + 1) {
#		if (defined @tmp[$index]) {
#			@tmp[$index] = @tmp[$index] + @subject_no_category[$index];
#		} else {
#			@tmp[$index] = @subject_no_category[$index];
#		}
#		@subject_no_category[$index] = 0;
#	}
#	$update_count[$group_id] = join "/", @tmp;	
	
	return 1;
}

return 1;
