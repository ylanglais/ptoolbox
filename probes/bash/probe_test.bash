#!/usr/bin/bash
. ./probe.lib
echo "pinging:"
_curl_post '{"req": "ping"}' | grep -v "^[ 	]*$"
echo ""
echo "test: add a:1 b:2"
_curl_post '{"req": "test", "action": "add", "a": 1, "b": 2}' | grep -v "^[ 	]*$"
echo ""
echo "test: count a:["one", "two", "three", "four", "five"]"
_curl_post '{"req": "test", "action": "count", "a": ["one", "two", "three", "four", "five"]}' | grep -v "^[ 	]*$"
echo ""
echo "probe: ping.mping():"
probe_send "ping" "mping" 1  | grep -v "^[ 	]*$"
echo ""

