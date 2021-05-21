#!/usr/bin/perl

# Last modified : 2007. 6. 20

our $max_rss;
our $support_rss;
our $kfm_rw;
our $host_name_web;
sub make_head {
	my $head;
	my $xml_version = '1.0';
	my $encoding = 'euc-kr';
	my $rss_version = '2.0';
	my %date = &get_date(time);
	my $lang = 'ko';

	$head = "<?xml version=\"$xml_version\" encoding=\"$encoding\" ?>\n";
	$head = "$head<rss version=\"$rss_version\">\n";
	$head = "$head\t<channel>\n";
	$head = "$head\t\t<title>gitstat ver$gitstat_ver</title>\n";
	$head = "$head\t\t<link>$host_name_web</link>\n";
	$head = "$head\t\t<description>gitstat's RSS</description>\n";
	$head = "$head\t\t<language>$lang</language>\n";
	$head = "$head\t\t<lastBuildDate>$date{'rfc2822'}</lastBuildDate>\n";

	return $head;
}

sub make_tail {
	my $tail = qq!	</channel>\n!;
	$tail = qq!$tail</rss>!;

	return $tail;
}

sub genRSS {

	if ($support_rss == 0) {
		return 1;
	}

	my $ref = shift;
	my @subject = @$ref;
	$ref = shift;
	my @content = @$ref;
	$ref = shift;
	my @link = @$ref;
	$ref = shift;
	my @author = @$ref;
	$ref = shift;
	my @epoch = @$ref;
	$ref = shift;
	my @category = @$ref;
	$ref = shift;
	my $count = @$ref;

	my $head;
	my @body;
	my $body;
	my $tail;
	my %date;
	my $index;
	my $item_count = 0;
	my $skip_sw = 0;
	my @rss;

	if (!(-e $kfm_rw."/rss/gitstat.rss")) {
		$head = make_head();
		$tail = make_tail();
	} else {
		open FILE, "<$kfm_rw"."/rss/gitstat.rss"
			or &klog('RSS File open error!', $log, 0);
		@rss = <FILE>;
		close(FILE);

		foreach (@rss) {
			if ($_ =~ m/<item>/) {
				$item_count = $item_count + 1;
			}
		}
	}

	$item = '';
	
	if ($#subject > $max_rss) {
		$item_count = $max_rss;
	} else {
		$item_count = $#subject;
	}


	for ($index = 0; $index <= $item_count; $index = $index + 1) {
		@content[$index] =~ s/\\n/<BR>/eg;
		%date = &get_date(@epoch[$index]);
		$item = "\t\t<item>\n";
		$item = "$item\t\t\t<title>".&esc_html(@subject[$index])."</title>\n";
		$item = "$item\t\t\t<author>".&esc_html(@author[$index])."</author>\n";
		$item = "$item\t\t\t<pubDate>$date{'rfc2822'}</pubDate>\n";
		$item = "$item\t\t\t<guid isPermaLink=\"true\">$host_name_web/".'commit-detail.php?commit='.@link[$index]."</guid>\n";
		$item = "$item\t\t\t<link>$host_name_web/".'commit-detail.php?commit='.@link[$index]."</link>\n";
		$item = "$item\t\t\t<description><![CDATA[<pre>";
		$item = "$item".@content[$index]."</pre>]]>\n\t\t\t</description>\n";
		$item = "$item\t\t</item>\n";

		push @body, $item;
	}

	$index = 0;
	$item_count = $max_rss - ($#body + 1);

	for ($index = 0; defined @rss[$index]; $index = $index + 1) {
		if (@rss[$index] =~ m!<\?xml!) {
			$head = '';
			while (!(@rss[$index] =~ m!<item>!)) {
				$head = $head.@rss[$index];
				$index = $index + 1;
				if (!defined @rss[$index]) {
					&klog('RSS File format error.', $log, 1);
					return 0;
				}
			}
		} 
		if (@rss[$index] =~ m!</channel>!) {
			$tail = '';
			while (!(@rss[$index] =~ m!</rss>!)) {
				$tail = $tail.@rss[$index];
				$index = $index + 1;
				if (!defined @rss[$index]) {
					&klog('RSS File format error.', $log, 1);
					return 0;
				}
			}
			$tail = $tail.@rss[$index];
		} 
		if ($item_count == 0) {
			$skip_sw = 1;
		}
		if (@rss[$index] =~ m/<item>/ & $skip_sw == 0) {
			$item = '';
			while (!(@rss[$index] =~ m!</item>!)) {
				$item = $item.@rss[$index];
				$index = $index + 1;
				if (!defined @rss[$index]) {
					&klog('RSS File format error.', $log, 1);
					return 0;
				}
			}
			$item = $item.@rss[$index];
			push @body, $item;
			$item_count = $item_count - 1;
		}
	}

	$body = join "", @body;

	open FILE, ">$kfm_rw".'/rss/gitstat.rss'
		or &klog('RSS File create error!', $log, 0);

	print FILE $head if (defined $head);
	print FILE $body;
	print FILE $tail if (defined $tail);

	close FILE;

	return 1;

}

return 1;
