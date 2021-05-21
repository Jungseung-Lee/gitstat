#!/usr/bin/perl

# Last modified : 2007. 6. 20
# Get user information as array or ref of array

sub getUserInfo {
	my $dbh = shift;
	my $sql;
	my $sth;
	my $result;
	my @row;
	my @user;
	my $ref;
	my $idx = 0;

	$sql = "SELECT no, email,mailhtml FROM Members";
	$sth = $dbh->prepare($sql);
	$result = $sth->execute();
	if ($result > 0) {
		while ($result > 0) {
			my %user;
			@row = $sth->fetchrow_array();
			$user{'idx'} = $idx;
			$user{'user_no'} = @row[0];
			$user{'email'} = @row[1];
			$user{'mailhtml'} = @row[2];
			
			push @user, \%user;
			$result = $result - 1;
			$idx = $idx + 1;
		}
	}

	return wantarray ? @user : \@user;
}

return 1;
