#!/usr/bin/bash
. ./probe.lib
echo "probe: hostdata.host_list():"
r=$(probe_send "hostdata" "host_list" 1)
lst=$(echo $r | jq -c '.data.list[]') 
echo $lst
for i in $lst 
do 
	host=$(echo $i | jq -r .name)
	os=$(echo $i | jq -r .distrib)
	rep=$(ssh -q root@$host '. /etc/os-release ; echo  "$ID;$VERSION_ID"');
	osstring=$(echo $rep|cut -d";" -f1)
	version=$(echo $rep|cut -d";" -f2)
echo "update hostdata: {\"name\":\"$host\", \"osstring\": \"$osstring\", \"version\":\"$version\"}"
	v=$(probe_send "hostdata" "host_put" "{\"name\":\"$host\", \"osstring\": \"$osstring\", \"version\":\"$version\"}")
	[ $(echo $v | jq -r '.msg') = 'ok' ] || {
		echo "Error: " $(  echo $v | jq -r '.msg') $(  echo $v | jq -r '.data')
	}

done
