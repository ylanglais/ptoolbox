#!/usr/bin/bash
. ./probe.lib
echo "probe: majos.list_get():"
r=$(probe_send "majos" "host_list" 1)
lst=$(echo $r | jq -c '.data.list[]') 

stamp=$(date +"%Y-%m-%d")
for i in $lst 
do 
	host=$(echo $i | jq -r .name)
	os=$(echo $i | jq -r .distrib)

	case $os in
	"UBUNTU" | "DEBIAN")
		rep=$(ssh -q root@$host "dpkg -l | tail -n +6 | wc -l;apt-get --just-print upgrade | grep '^Inst' | wc -l")
		;;
	"CENTOS"|"OL"|"ORACLE"|"REDHAT"|"SUZE")
		rep=$(ssh -q root@$host "rpm -qa | wc -l; yum check-update | tail -n +4 | wc -l")
		;;
	*)
		;;
	esac
	[ -n "$rep" ] && {
		let n=n+1
		[ $n -gt 1 ] && d="$d,"
		nbpkg=$(echo $rep | cut -d" "  -f1)
		nbmaj=$(echo $rep | cut -d" "  -f2)
		echo "$host -> $nbpkg / $nbmaj"
		v=$(probe_send "majos" "save_all" "{\"host\": \"$host\",\"nbpkg\":$nbpkg,\"nbmaj\":$nbmaj, \"stamp\":\"$stamp\"}")
		[ $(echo $v | jq -r '.msg') = 'ok' ] || {
			echo "Error: " $(  echo $v | jq -r '.msg') $(  echo $v | jq -r '.data')
		}
	} 
done
