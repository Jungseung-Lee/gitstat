#!/usr/bin/perl

# It maintains Log files...
#
# sub klog(<log_msg>, <debug>, <return>);
#
# Last modified : 2007. 6. 20.

sub klog {
	my $log_comment = shift;
	my $log = shift;
	my $mode = shift || 0;
	my $filename;

	if ($log == 0 && $mode == 0) { die $log_comment; }
	elsif ($log == 0 && $mode == 1) { return; }

	my ($sec, $min, $hour, $date, $month, $year, $weekday, $yearday, $lsdst) = localtime(time());
	$month = $month + 1;
	$year = $year + 1900;
	
	my @dayname = ("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
	my $str_weekday = $dayname[$weekday];
	my $current = sprintf "%4d. %2d. %2d (%3s) %02d:%02d:%02d", $year, $month, $date, $str_weekday, $hour, $min, $sec;
	my $msg = sprintf "%s : %s\n", $current, $log_comment;		# You can modify this to fit you...

	$filename = sprintf "gitstat%02d%02d.log", $month, $date;

	open FILE, ">>$kfm_rw/log/$filename";
	print FILE $msg;	
	close FILE;

	if ($mode == 0) { die $log_comment; }
	else { return; }		# mode = 0 : terminates program / mode = 1 : just write log and return..
}

return 1;
