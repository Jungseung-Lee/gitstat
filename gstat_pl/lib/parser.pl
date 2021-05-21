#!/usr/bin/perl

# It parses ChangeLog, Tag information.
#
# sub parse_commit_text			: parses one commit
# sub parse_commit			: get required information to parse and call parse_commit_text()
# sub parse_difftree_raw_line		: parses diff-tree information's one row
# sub parse_tag				: parses ont tag
#
# Last modified : 2007. 6. 20


sub parse_commit_text {
	my ($commit_text, $withparents) = @_;
	my @commit_lines = split '\n', $commit_text;
	my %co;

	pop @commit_lines; 		# Remove '\0'

	my $header = shift @commit_lines;
	if (!($header =~ m/^[0-9a-fA-F]{40}/)) {
		return;
	}
	($co{'id'}, my @parents) = split ' ', $header;
	while (my $line = shift @commit_lines) {
		last if $line eq "\n";
		if ($line =~ m/^tree ([0-9a-fA-F]{40})$/) {
			$co{'tree'} = $1;
		} elsif ((!defined $withparents) && ($line =~ m/^parent ([0-9a-fA-F]{40})$/)) {
			push @parents, $1;
		} elsif ($line =~ m/^author (.*) ([0-9]+) (.*)$/) {
			$co{'author'} = $1;
			$co{'author_epoch'} = $2;
			$co{'author_tz'} = $3;
			if ($co{'author'} =~ m/^([^<]+) <([^>]*)>/) {
				$co{'author_name'}  = $1;
				$co{'author_email'} = $2;
			} else {
				$co{'author_name'} = $co{'author'};
			}
		} elsif ($line =~ m/^committer (.*) ([0-9]+) (.*)$/) {
			$co{'committer'} = $1;
			$co{'committer_epoch'} = $2;
			$co{'committer_tz'} = $3;
			$co{'committer_name'} = $co{'committer'};
			if ($co{'committer'} =~ m/^([^<]+) <([^>]*)>/) {
				$co{'committer_name'}  = $1;
				$co{'committer_email'} = $2;
			} else {
				$co{'committer_name'} = $co{'committer'};
			}
		}
	}
	if (!defined $co{'tree'}) {
		return;
	};
	$co{'parents'} = \@parents;
	$co{'parent'} = $parents[0];

	foreach my $title (@commit_lines) {
		$title =~ s/^    //;
		if ($title ne "") {
			$co{'title'} = chop_str($title, 80, 5);
			# remove leading stuff of merges to make the interesting part visible
			if (length($title) > 50) {
				$title =~ s/^Automatic //;
				$title =~ s/^merge (of|with) /Merge ... /i;
				if (length($title) > 50) {
					$title =~ s/(http|rsync):\/\///;
				}
				if (length($title) > 50) {
					$title =~ s/(master|www|rsync)\.//;
				}
				if (length($title) > 50) {
					$title =~ s/kernel.org:?//;
				}
				if (length($title) > 50) {
					$title =~ s/\/pub\/scm//;
				}
			}
			$co{'title_short'} = chop_str($title, 50, 5);
			last;
		}
	}
	if ($co{'title'} eq "") {
		$co{'title'} = $co{'title_short'} = '(no commit message)';
	}
	# remove added spaces
	foreach my $line (@commit_lines) {
		$line =~ s/^    //;
	}
	$co{'comment'} = \@commit_lines;

	return %co;
}

sub parse_commit {
	my ($commit_id) = @_;
	my %co;

	local $/ = "\0";

	open my $fd, "-|", git_cmd(), "rev-list",
		"--parents",
		"--header",
		"--max-count=1",
		$commit_id,
		"--",
		or die_error(undef, "Open git-rev-list failed");
	%co = parse_commit_text(<$fd>, 1);
	close $fd;

	return %co;
}

sub unquote {
	my $str = shift;
	return $str;
}

sub parse_difftree_raw_line {
	my $line = shift;
	my %res;

	# ':100644 100644 03b218260e99b78c6df0ed378e59ed9205ccc96d 3b93d5e7cc7f7dd4ebed13a5cc1a4ad976fc94d8 M	ls-files.c'
	# ':100644 100644 7f9281985086971d3877aca27704f2aaf9c448ce bc190ebc71bbd923f2b728e505408f5e54bd073a M	rev-tree.c'
	if ($line =~ m/^:([0-7]{6}) ([0-7]{6}) ([0-9a-fA-F]{40}) ([0-9a-fA-F]{40}) (.)([0-9]{0,3})\t(.*)$/) {
		$res{'from_mode'} = $1;
		$res{'to_mode'} = $2;
		$res{'from_id'} = $3;
		$res{'to_id'} = $4;
		$res{'status'} = $5;
		$res{'similarity'} = $6;
		if ($res{'status'} eq 'R' || $res{'status'} eq 'C') { # renamed or copied
			($res{'from_file'}, $res{'to_file'}) = map { unquote($_) } split("\t", $7);
		} else {
			$res{'file'} = unquote($7);
		}
	}
	# 'c512b523472485aef4fff9e57b229d9d243c967f'
	elsif ($line =~ m/^([0-9a-fA-F]{40})$/) {
		$res{'commit'} = $1;
	}

	return wantarray ? %res : \%res;
}

sub parse_tag {
	my $tag_id = shift;
	my %tag;
	my @comment;

	open my $fd, "-|", git_cmd(), "cat-file", "tag", $tag_id or return;
	$tag{'id'} = $tag_id;
	while (my $line = <$fd>) {
		chomp $line;
		if ($line =~ m/^object ([0-9a-fA-F]{40})$/) {
			$tag{'object'} = $1;
		} elsif ($line =~ m/^type (.+)$/) {
			$tag{'type'} = $1;
		} elsif ($line =~ m/^tag (.+)$/) {
			$tag{'name'} = $1;
		} elsif ($line =~ m/^tagger (.*) ([0-9]+) (.*)$/) {
			# Do nothing
		} elsif ($line =~ m/--BEGIN/) {
			push @comment, $line;
			last;
		} elsif ($line eq "") {
			last;
		}
	}
	push @comment, <$fd>;
	$tag{'comment'} = \@comment;
	close $fd or return;

	# author, epoch and tz should be retrieved from the commit referenced by the tag
        # but only if the tag appears to be valid. The tag for linux kernel v2.16.11
        # is an example for a not so valid tag.
        if (defined $tag{'type'} && $tag{'type'} eq "commit") {
		open my $fd, "-|", git_cmd(), "cat-file", "commit", $tag{'object'} or return;
		while (my $line = <$fd>) {
			chomp $line;
			if ($line =~ m/^committer (.*) ([0-9]+) (.*)$/) {
				$tag{'author'} = $1;
				$tag{'epoch'} = $2;
				$tag{'tz'} = $3;
			}
		}
		close $fd or return;
	}

	if (!defined $tag{'name'}) {
		return
	};
	return %tag
}

return 1;
