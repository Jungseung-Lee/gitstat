#!/usr/bin/perl

require 'config.pl';
require 'lib/log.pl';
require 'lib/lib.pl';

our $max_rss;
our $support_rss;

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
	$head = "$head\t\t<title>Gitstat $gitstat_ver</title>\n";
	$head = "$head\t\t<link>$host_name_web</link>\n";
	$head = "$head\t\t<description>Gitstat's RSS</description>\n";
	$head = "$head\t\t<language>$lang</language>\n";
	$head = "$head\t\t<lastBuildDate>$date{'rfc2822'}</lastBuildDate>\n";

	return $head;
}

sub make_tail {
	my $tail = qq!	</channel>\n!;
	$tail = qq!$tail</rss>!;
}

sub cutRSS {

	my $head;
	my @body;
	my $body;
	my $tail;
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

	$index = 0;
	$item_count = $max_rss;

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
			if ($item_count == 0) {
				$skip_sw = 1;
			}
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

cutRSS();
