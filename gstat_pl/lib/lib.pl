#!/usr/bin/perl

require 'lib/log.pl';

# Gitstat's Library file
#
# sub chop_str
# sub git_cmd
# sub get_tags_list
# sub recent_commit
#
# Last modified : 2007. 9. 05

our $log;
our $gitstat_ver ="v0.5";

sub chop_str {
	my $str = shift;
	my $len = shift;
	my $add_len = shift || 10;

	my $body;
	my $tail;

	# Allow only $len chars, but don't cut a word if it would fit in $add_len.
	# If it doens't fit, cut it if it's still longer than the dots we would add.

	$str =~ m/^(.{0,$len}[^ \/\-_:\.@]{0,$add_len})(.*)/;

	$body = $1;
	$tail = $2;
	if (length($tail) > 4) {
		$tail = " ...";
		$body =~ s/&[^;]*$//;		# remove chopped character entities.
	}

	return "$body$tail";
}

# very thin wrapper for decode("utf8", $str, Encode::FB_DEFAULT);
sub to_utf8 {
	my $str = shift;
	return decode("utf8", $str, Encode::FB_DEFAULT);
}

# Make control characters "printable", using character escape codes (CEC)
sub quot_cec {
	my $cntrl = shift;
	my %es = ( 		# character escape codes, aka escape sequences
	    	"\t" => '\t',   # tab            (HT)
   		"\n" => '\n',   # line feed      (LF)
   		"\r" => '\r',   # carrige return (CR)
		"\f" => '\f',   # form feed      (FF)
    		"\b" => '\b',   # backspace      (BS)
    		"\a" => '\a',   # alarm (bell)   (BEL)
    		"\e" => '\e',   # escape         (ESC)
	    	"\013" => '\v', # vertical tab   (VT)
    		"\000" => '\0', # nul character  (NUL)
    	);
	my $chr = ( (exists $es{$cntrl}) ? $es{$cntrl} : sprintf('\%03o', ord($cntrl)) );
 	return "<span class=\"cntrl\">$chr</span>";
}

# replace invalid utf8 character with SUBSTITUTION sequence
sub esc_html ($;%) {
	my $str = shift;
	my %opts = @_;
	
	$str = to_utf8($str);
	$str = escapeHTML($str);
	
	if ($opts{'-nbsp'}) {
		$str =~ s/ /&nbsp;/g;
	}
	$str =~ s|([[:cntrl:]])|(($1 ne "\t") ? quot_cec($1) : $1)|eg;
	return $str;
}

sub git_cmd {
	return "git", '--git-dir='.$repository;
}

sub get_tags_list {
	my @tagslist;
	my $line;
	my $fd;
	my $id, $type;

	open $fd, '-|', git_cmd(), 'for-each-ref', '--sort=-*creatordate',
		'--format=%(objectname) %(objecttype)', 'refs/tags' or return;

	while ($line = <$fd>) {
		chomp $line;
		($id, $type) = split(' ', $line, 2);
		if ($type eq "tag") 
		{ 
			if (!exist_array(\@tagslist, $id)) {
				push @tagslist, $id; 
			}
		}

	}

	close $fd;

	return @tagslist;
}

sub recent_commit {
	my @r_commit;
	my $fd;
	open $fd, "-|", git_cmd(), "show", "--pretty=oneline" or die("Open git-show failed");
	@r_commit = split ' ', <$fd>;
	close $fd;
	
	return $r_commit[0];
}

sub exist_db {		# Check the tuple equal to argument information in DB.
	my ($dbh, $tb, $equation) = @_;
	my $sql;
	my $sth;
	my $result;

	$sql = "SELECT * FROM $tb WHERE $equation";
	$sth = $dbh->prepare($sql) || &klog($dbh->errstr, $log, 0);
	$result = $sth->execute();

	$sth->finish();

	if ($result == 0) { return 0; }
	else { return 1; }
}

sub select_db {		# Select tuples in DB. (Test version... needs fixing) 
	my ($dbh, $list, $tb, $equation) = @_;
	my $sql;
	my $sth;
	my @result;
	
	$sql = "SELECT $list FROM $tb WHERE $equation";
	$sth = $dbh->prepare($sql) || return -1;
	$result = $sth->execute();
	
	if ($result == 0) { 
		$sth->finish();
		return;
	}
	else {
		@result = $sth->fetchrow_array();
		$sth->finish();
		return @result;
	}

	$sth->finish();

}

sub insert_db {		# Insert tuple into DB. (also test version... needs fixing)
	my ($dbh, $tb, $list, $values_list) = @_;
	my $sth;
	my $sql;

	$sql = "INSERT INTO $tb ($list) values ($values_list)";
	$sth = $dbh->prepare($sql) || return -1;
	$sth->execute();

	$sth->finish();

	return 1;
}

sub exist_array {
	my $ref = shift;
	my $str = shift;
	my @array = @$ref;

	foreach (@array) {
		if ($str eq $_) {
			return 1;
		}
		else {
			next;
		}
	}
	return 0;
}

sub get_date {
	my $epoch = shift;
	my $tz = shift || "-0000";

	my %date;
	my @months = ("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
	my @days = ("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
	my ($sec, $min, $hour, $mday, $mon, $year, $wday, $yday) = gmtime($epoch);
	
	$date{'hour'} = $hour;
	$date{'minute'} = $min;
	$date{'mday'} = $mday;
	$date{'day'} = $days[$wday];
	$date{'month'} = $months[$mon];

	$date{'rfc2822'}   = sprintf "%s, %d %s %4d %02d:%02d:%02d +0000", $days[$wday], $mday, $months[$mon], 1900+$year, $hour ,$min, $sec;

	$date{'mday-time'} = sprintf "%d %s %02d:%02d", $mday, $months[$mon], $hour ,$min;

	$date{'iso-8601'}  = sprintf "%04d-%02d-%02dT%02d:%02d:%02dZ", 1900+$year, $mon, $mday, $hour ,$min, $sec;

	$tz =~ m/^([+\-][0-9][0-9])([0-9][0-9])$/;

	my $local = $epoch + ((int $1 + ($2/60)) * 3600);
	($sec, $min, $hour, $mday, $mon, $year, $wday, $yday) = gmtime($local);

	$date{'hour_local'} = $hour;

	$date{'minute_local'} = $min;

	$date{'tz_local'} = $tz;

	$date{'iso-tz'} = sprintf("%04d-%02d-%02d %02d:%02d:%02d %s", 1900+$year, $mon+1, $mday, $hour, $min, $sec, $tz);

	return %date;
}

sub getVersion {
	my $dbh = shift;
	my $commit = shift;
	my $sql;
	my $sth;

	$sql = 'SELECT no FROM v_tag WHERE object = ?';
	$sth = $dbh->prepare($sql)
		or return -1;
	$result = $sth->execute($commit);

	if ($result == 1) {
		@result = $sth->fetchrow_array();
		return @result[0];
	} elsif ($result == 0) {
		return;
	} else {
		return -1;
	}
}

sub remove {		# For removal of ChangeLog
			# ChangeLog has relation With LogCategory...
	return 1;
}

sub get_max {		# Get maximum value among array elements.
	my $ref = shift;
	my @array = @$ref;
	my $max = -1;

	foreach (@array) {
		if ($max < $_) {
			$max = $_;
		} else {
			next;
		}
	}

	return $max;
}


sub binary_search {	# Ing...
	my $ref = shift;
	my @array = @$ref;
	my $key = shift;
	my $first = shift || 0;
	my $last = shift || $#array;
	my $mid = ($last + $first) / 2;
	while ($first < $last) {
		if ($array[$mid] > $key) {
			search(\@array, $key, $mid, $last);
		} elsif ($array[$mid] <= $key) {
			search(\@array, $key, $first, $mid);
		}
	}
	return $mid;
}

return 1;
